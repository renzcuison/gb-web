<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $role = (string) $request->input('role', 'all');

        $query = User::query()
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name');

        if ($role === 'admin') {
            $query->where('role', 'admin');
        } elseif ($role === 'user') {
            $query->where(function ($q) {
                $q->whereNull('role')->orWhere('role', '!=', 'admin');
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();

        $stats = [
            'total' => $totalUsers,
            'admins' => $adminCount,
            'members' => max(0, $totalUsers - $adminCount),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $query->paginate(15)->withQueryString(),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'role' => $role,
            ],
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
        ]);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $newRole = $validated['role'];
        $currentRole = $user->isAdmin() ? 'admin' : 'user';

        if ($currentRole === $newRole) {
            return back();
        }

        if ($currentRole === 'admin' && $newRole !== 'admin') {
            $remainingAdmins = User::where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingAdmins === 0) {
                return back()->with('error', 'At least one admin account must remain active.');
            }
        }

        $user->update(['role' => $newRole]);

        $label = $newRole === 'admin' ? 'Admin' : 'Member';

        return back()->with('success', "Updated {$user->name} to {$label}.");
    }
}