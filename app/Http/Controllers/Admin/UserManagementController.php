<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * User Management Controller
 * 
 * Handles all user management operations for administrators.
 */
class UserManagementController extends Controller
{
    /**
     * Display a listing of users with pagination and filters
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by premium status
        if ($request->filled('premium')) {
            $query->where('is_premium', $request->premium === 'true');
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('is_active', $request->active === 'true');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $users = $query->withCount('generations')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'premium', 'active', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Display the specified user details
     */
    public function show(User $user)
    {
        $user->load(['generations' => function ($query) {
            $query->latest()->take(10);
        }]);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user,
            'statistics' => [
                'total_generations' => $user->generations()->count(),
                'completed_generations' => $user->generations()->where('status', 'completed')->count(),
                'failed_generations' => $user->generations()->where('status', 'failed')->count(),
                'total_credits_used' => $user->generations()->sum('credits_used'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'credits' => ['required', 'integer', 'min:0'],
            'is_premium' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Track if premium status changed
        $premiumChanged = $user->is_premium !== $validated['is_premium'];
        
        // Track if active status changed
        $activeChanged = $user->is_active !== $validated['is_active'];
        
        $user->update($validated);

        // Update suspended_at timestamp if status changed
        if ($activeChanged && !$validated['is_active']) {
            $user->update(['suspended_at' => now()]);
        } elseif ($activeChanged && $validated['is_active']) {
            $user->update(['suspended_at' => null]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Adjust user credits
     */
    public function adjustCredits(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $oldCredits = $user->credits;
        $user->credits += $validated['amount'];
        
        // Ensure credits don't go negative
        if ($user->credits < 0) {
            $user->credits = 0;
        }
        
        $user->save();

        // Log the transaction (if CreditTransaction model exists)
        // CreditTransaction::create([...]);

        return redirect()->back()->with('success', "Credits berhasil disesuaikan dari {$oldCredits} menjadi {$user->credits}.");
    }

    /**
     * Toggle user premium status
     */
    public function togglePremium(User $user)
    {
        $user->is_premium = !$user->is_premium;
        $user->save();

        $status = $user->is_premium ? 'Premium' : 'Free';
        return redirect()->back()->with('success', "Status user berhasil diubah menjadi {$status}.");
    }

    /**
     * Toggle user active status (suspend/activate)
     */
    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->suspended_at = $user->is_active ? null : now();
        $user->save();

        $status = $user->is_active ? 'Aktif' : 'Ditangguhkan';
        return redirect()->back()->with('success', "Status user berhasil diubah menjadi {$status}.");
    }

    /**
     * Remove the specified user (soft delete or hard delete)
     */
    public function destroy(User $user)
    {
        // Prevent deleting admin users
        if ($user->is_admin) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user admin.');
        }

        // Check if user has active generations
        if ($user->generations()->where('status', 'in_progress')->exists()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user dengan generation yang sedang berjalan.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
