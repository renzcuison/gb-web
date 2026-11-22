<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceivedMail;
use App\Models\Inquiry;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\NewInquiryNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:160'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        if (!empty($validated['product_variant_id'])) {
            $belongsToProduct = ProductVariant::where('id', $validated['product_variant_id'])
                ->where('product_id', $validated['product_id'])
                ->exists();

            if (!$belongsToProduct) {
                return back()->withErrors([
                    'product_variant_id' => 'The selected option does not belong to this product.',
                ]);
            }
        }

        $inquiry = Inquiry::create([
            ...$validated,
            'status' => Inquiry::STATUS_NEW,
        ]);

        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewInquiryNotification($inquiry));
        }

        try {
            Mail::to($inquiry->customer_email)->send(new InquiryReceivedMail($inquiry));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Your inquiry has been received. Our team will reach out shortly.');
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $status = (string) $request->input('status', 'new');

        $query = Inquiry::with([
            'product:id,name,slug,price',
            'product.media' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('order'),
            'variant:id,product_id,sku,variant_label,price_override,stock_qty',
            'resolvedBy:id,name',
        ])->latest();

        if ($status !== 'all' && in_array($status, [
            Inquiry::STATUS_NEW,
            Inquiry::STATUS_CONTACTED,
            Inquiry::STATUS_RESOLVED,
            Inquiry::STATUS_ARCHIVED,
        ], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('variant', fn ($vq) => $vq->where('sku', 'like', "%{$search}%"));
            });
        }

        $stats = [
            'new' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
            'contacted' => Inquiry::where('status', Inquiry::STATUS_CONTACTED)->count(),
            'resolved' => Inquiry::where('status', Inquiry::STATUS_RESOLVED)->count(),
            'total' => Inquiry::count(),
        ];

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => $query->paginate(15)->withQueryString(),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in([
                Inquiry::STATUS_NEW,
                Inquiry::STATUS_CONTACTED,
                Inquiry::STATUS_RESOLVED,
                Inquiry::STATUS_ARCHIVED,
            ])],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $updates = [
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
        ];

        if ($validated['status'] === Inquiry::STATUS_RESOLVED && $inquiry->status !== Inquiry::STATUS_RESOLVED) {
            $updates['resolved_at'] = now();
            $updates['resolved_by'] = $request->user()->id;
        } elseif ($validated['status'] !== Inquiry::STATUS_RESOLVED) {
            $updates['resolved_at'] = null;
            $updates['resolved_by'] = null;
        }

        $inquiry->update($updates);

        return back()->with('success', 'Inquiry updated.');
    }

    public function fulfill(Request $request, Inquiry $inquiry): RedirectResponse
    {
        if ($inquiry->status === Inquiry::STATUS_RESOLVED) {
            return back()->withErrors([
                'fulfill' => 'This inquiry has already been resolved.',
            ]);
        }

        if (!$inquiry->product_variant_id) {
            return back()->withErrors([
                'fulfill' => 'No specific variant is attached to this inquiry.',
            ]);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($request, $inquiry, $validated) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($inquiry->product_variant_id);

                if ($variant->stock_qty < $inquiry->quantity) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$variant->sku} ({$variant->stock_qty} available, {$inquiry->quantity} requested)."
                    );
                }

                $variant->stock_qty -= $inquiry->quantity;
                $variant->save();

                InventoryMovement::create([
                    'variant_id' => $variant->id,
                    'user_id' => $request->user()->id,
                    'quantity' => $inquiry->quantity,
                    'type' => 'out',
                    'reason' => "Inquiry #{$inquiry->id} fulfilled ({$inquiry->customer_name})",
                ]);

                $fulfillmentNote = "Fulfilled {$inquiry->quantity} unit(s) from SKU {$variant->sku} on " . now()->format('M d, Y H:i') . '.';
                $existingNotes = trim((string) ($validated['admin_notes'] ?? $inquiry->admin_notes ?? ''));
                $combinedNotes = $existingNotes !== '' ? "{$existingNotes}\n{$fulfillmentNote}" : $fulfillmentNote;

                $inquiry->update([
                    'status' => Inquiry::STATUS_RESOLVED,
                    'admin_notes' => $combinedNotes,
                    'resolved_at' => now(),
                    'resolved_by' => $request->user()->id,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'fulfill' => $e->getMessage(),
            ]);
        }

        return back()->with('success', "Deducted {$inquiry->quantity} unit(s) and marked inquiry #{$inquiry->id} resolved.");
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $inquiry->delete();

        return back()->with('success', 'Inquiry removed.');
    }
}