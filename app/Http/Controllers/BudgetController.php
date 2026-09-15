<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetAllocationRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\BudgetAllocation;
use App\Models\Payment;
use App\Models\Team;
use App\Models\VendorCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Team $current_team): Response|RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $categories = VendorCategory::all();

        // Ensure allocations exist for each category
        foreach ($categories as $cat) {
            BudgetAllocation::firstOrCreate(
                [
                    'wedding_project_id' => $project->id,
                    'vendor_category_id' => $cat->id,
                ],
                [
                    'percentage' => $cat->default_percentage,
                    'allocated_amount_idr' => ($project->budget_total * $cat->default_percentage) / 100,
                ]
            );
        }

        $allocations = BudgetAllocation::where('wedding_project_id', $project->id)
            ->with(['vendorCategory', 'payments'])
            ->get();

        $payments = Payment::where('wedding_project_id', $project->id)
            ->with(['vendor', 'budgetAllocation'])
            ->latest('payment_date')
            ->get();

        $vendors = $project->vendors()->select('id', 'name', 'vendor_category_id')->get();

        return Inertia::render('Budget/Index', [
            'weddingProject' => $project,
            'categories' => $categories,
            'allocations' => $allocations,
            'payments' => $payments,
            'vendors' => $vendors,
        ]);
    }

    public function updateAllocations(StoreBudgetAllocationRequest $request, Team $current_team): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        foreach ($request->validated('allocations') as $item) {
            BudgetAllocation::updateOrCreate(
                [
                    'wedding_project_id' => $project->id,
                    'vendor_category_id' => $item['vendor_category_id'],
                ],
                [
                    'percentage' => $item['percentage'],
                    'allocated_amount_idr' => $item['allocated_amount_idr'],
                    'notes' => $item['notes'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Alokasi budget berhasil diperbarui');
    }

    public function storePayment(StorePaymentRequest $request, Team $current_team): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project) {
            return redirect()->route('onboarding.show', $current_team);
        }

        $data = $request->validated();
        $data['wedding_project_id'] = $project->id;

        Payment::create($data);

        return back()->with('success', 'Catatan pembayaran berhasil ditambahkan');
    }

    public function destroyPayment(Team $current_team, Payment $payment): RedirectResponse
    {
        $project = $current_team->weddingProject;
        if (! $project || $payment->wedding_project_id !== $project->id) {
            abort(404);
        }

        $this->authorize('delete', $payment);

        $this->logDeleted($payment, $project->id, 'Pembayaran dihapus: '.$payment->title);
        $payment->delete();

        return back()->with('success', 'Pembayaran berhasil dihapus');
    }
}
