<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCreditPackageRequest;
use App\Http\Requests\UpdateCreditPackageRequest;
use App\Models\CreditPackage;
use Inertia\Inertia;

/**
 * Credit Package Management Controller
 *
 * Allows admins to create, update, and soft-delete credit packages
 * that users can purchase via Mayar.
 */
class CreditPackageController extends Controller
{
    /**
     * Display a listing of all credit packages.
     */
    public function index(): \Inertia\Response
    {
        $packages = CreditPackage::withTrashed()
            ->ordered()
            ->get()
            ->map(fn (CreditPackage $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'credits' => $p->credits,
                'price' => $p->price,
                'formatted_price' => $p->formattedPrice(),
                'is_active' => $p->is_active,
                'sort_order' => $p->sort_order,
                'deleted_at' => $p->deleted_at?->toDateTimeString(),
                'created_at' => $p->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/CreditPackages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new package.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/CreditPackages/Form', [
            'package' => null,
        ]);
    }

    /**
     * Store a newly created package.
     */
    public function store(StoreCreditPackageRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        CreditPackage::create($data);

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(CreditPackage $creditPackage): \Inertia\Response
    {
        return Inertia::render('Admin/CreditPackages/Form', [
            'package' => [
                'id' => $creditPackage->id,
                'name' => $creditPackage->name,
                'description' => $creditPackage->description,
                'credits' => $creditPackage->credits,
                'price' => $creditPackage->price,
                'is_active' => $creditPackage->is_active,
                'sort_order' => $creditPackage->sort_order,
            ],
        ]);
    }

    /**
     * Update the specified package.
     */
    public function update(UpdateCreditPackageRequest $request, CreditPackage $creditPackage): \Illuminate\Http\RedirectResponse
    {
        $creditPackage->update($request->validated());

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil diperbarui.');
    }

    /**
     * Soft-delete the specified package.
     */
    public function destroy(CreditPackage $creditPackage): \Illuminate\Http\RedirectResponse
    {
        $creditPackage->delete();

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil dihapus.');
    }

    /**
     * Restore a soft-deleted package.
     */
    public function restore(int $id): \Illuminate\Http\RedirectResponse
    {
        CreditPackage::withTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.credit-packages.index')
            ->with('success', 'Paket kredit berhasil dipulihkan.');
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(CreditPackage $creditPackage): \Illuminate\Http\RedirectResponse
    {
        $creditPackage->update(['is_active' => ! $creditPackage->is_active]);

        return back()->with('success', 'Status paket berhasil diubah.');
    }
}
