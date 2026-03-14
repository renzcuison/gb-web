<?php


namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = ProductVariant::with(['product.category', 'product.brand'])
            ->orderBy('stock_qty', 'asc')
            ->paginate(15);

        return Inertia::render('Inventory/Index', [
            'inventory' => $inventory
        ]);
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $variant = ProductVariant::lockForUpdate()->find($validated['variant_id']);

            InventoryMovement::create([
                'variant_id' => $variant->id,
                'user_id' => auth()->id(),
                'quantity' => $validated['quantity'],
                'type' => $validated['type'],
                'reason' => $validated['reason'],
            ]);

            if ($validated['type'] === 'in') {
                $variant->increment('stock_qty', $validated['quantity']);
            } else {
                $variant->decrement('stock_qty', $validated['quantity']);
            }
        });

        return back()->with('message', 'Inventory adjusted successfully.');
    }
}