<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Team;
use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    public function index(Team $current_team): Response|RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $categories = VendorCategory::all();
        $vendors = Vendor::where('wedding_project_id', $project->id)
            ->with(['vendorCategory', 'payments'])
            ->latest()
            ->get();

        return Inertia::render('Vendors/Index', [
            'weddingProject' => $project,
            'categories' => $categories,
            'vendors' => $vendors,
        ]);
    }

    public function store(StoreVendorRequest $request, Team $current_team): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $data = $request->validated();
        $data['wedding_project_id'] = $project->id;

        $vendor = Vendor::create($data);
        $this->logCreated($vendor, $project->id, 'Vendor ditambahkan: '.$vendor->name);

        return back()->with('success', 'Vendor berhasil ditambahkan');
    }

    public function update(UpdateVendorRequest $request, Team $current_team, Vendor $vendor): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $vendor->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('update', $vendor);

        $vendor->update($request->validated());
        $this->logUpdated($vendor, $project->id, 'Vendor diperbarui: '.$vendor->name);

        return back()->with('success', 'Vendor berhasil diperbarui');
    }

    public function destroy(Team $current_team, Vendor $vendor): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $vendor->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('delete', $vendor);

        $vendorName = $vendor->name;
        $this->logDeleted($vendor, $project->id, 'Vendor dihapus: '.$vendorName);
        $vendor->delete();

        return back()->with('success', 'Vendor berhasil dihapus');
    }
}
