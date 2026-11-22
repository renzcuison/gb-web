<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status', 'all');

        $query = Announcement::query();

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search !== '') {
            $query->where('content', 'like', "%{$search}%");
        }

        $announcements = $query
            ->orderByDesc('priority')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::where('is_active', true)->count(),
            'inactive' => Announcement::where('is_active', false)->count(),
            'high_priority' => Announcement::where('is_active', true)->where('priority', '>', 0)->count(),
        ];

        $activePreview = Announcement::where('is_active', true)
            ->orderByDesc('priority')
            ->latest()
            ->get(['id', 'content', 'priority']);

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
            'stats' => $stats,
            'activePreview' => $activePreview,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        Announcement::create($validated);

        return back()->with('success', 'Announcement created successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:255',
            'priority' => 'required|integer|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        $announcement->update($validated);

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => ! $announcement->is_active,
        ]);

        return back()->with('success', $announcement->is_active ? 'Announcement activated.' : 'Announcement deactivated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }
}