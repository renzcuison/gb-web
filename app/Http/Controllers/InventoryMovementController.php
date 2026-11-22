<?php

namespace App\Http\Controllers;

use App\Exports\InventoryMovementExport;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class InventoryMovementController extends Controller
{
    protected function buildBaseQuery(Request $request): Builder
    {
        $search = trim((string) $request->input('search', ''));
        $userId = $request->input('user');
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        $query = InventoryMovement::with([
            'variant' => fn ($q) => $q->withTrashed()->with([
                'product' => fn ($pq) => $pq->withTrashed(),
            ]),
            'user:id,name',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('variant', function ($vq) use ($search) {
                        $vq->withTrashed()
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhere('variant_label', 'like', "%{$search}%")
                            ->orWhereHas('product', fn ($pq) => $pq->withTrashed()->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type = $request->input('type', 'all');
        $userId = $request->input('user');
        $dateFrom = $request->input('from');
        $dateTo = $request->input('to');

        $baseQuery = $this->buildBaseQuery($request);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'in_count' => (clone $baseQuery)->where('type', 'in')->count(),
            'out_count' => (clone $baseQuery)->where('type', 'out')->count(),
            'units_in' => (int) (clone $baseQuery)->where('type', 'in')->sum('quantity'),
            'units_out' => (int) (clone $baseQuery)->where('type', 'out')->sum('quantity'),
        ];

        $query = clone $baseQuery;

        if (in_array($type, ['in', 'out'], true)) {
            $query->where('type', $type);
        }

        $movements = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $actors = User::whereIn(
            'id',
            InventoryMovement::whereNotNull('user_id')->distinct()->pluck('user_id')
        )
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Inventory/Movements', [
            'movements' => $movements,
            'stats' => $stats,
            'actors' => $actors,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'user' => $userId ? (int) $userId : null,
                'from' => $dateFrom ?: '',
                'to' => $dateTo ?: '',
            ],
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'all');
        $query = $this->buildBaseQuery($request);

        if (in_array($type, ['in', 'out'], true)) {
            $query->where('type', $type);
        }

        $query->latest();

        $format = $request->input('format', 'xlsx') === 'csv' ? 'csv' : 'xlsx';
        $writerType = $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;
        $filename = 'greatbuy-movements-' . now()->format('Y-m-d-His') . '.' . $format;

        return Excel::download(
            new InventoryMovementExport($query),
            $filename,
            $writerType
        );
    }
}