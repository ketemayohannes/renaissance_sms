<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryPurchaseRequest;
use App\Services\InventoryRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryPurchaseRequestController extends Controller
{
    public function __construct(private InventoryRequestService $service) {}

    /**
     * Approval queues — the Principal stage and the GM stage, shown to whichever the
     * viewer can act on (Super Admin sees both).
     */
    public function index()
    {
        $user = Auth::user();

        $awaitingPrincipal = $user->can('approve inventory requests')
            ? InventoryPurchaseRequest::awaitingPrincipal()->with('requester')->latest()->get()
            : collect();

        $awaitingGm = $user->can('approve inventory purchases')
            ? InventoryPurchaseRequest::awaitingGm()->with(['requester', 'principal'])->latest()->get()
            : collect();

        return view('admin.inventory.purchases.index', compact('awaitingPrincipal', 'awaitingGm'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'inventory_category_id' => 'nullable|exists:inventory_categories,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'estimated_unit_cost' => 'nullable|numeric|min:0',
            'justification' => 'required|string|max:1000',
        ]);

        $this->service->submitPurchaseRequest(Auth::user(), $validated);

        return back()->with('success', 'Purchase request submitted for approval.');
    }

    public function principalDecision(Request $request, InventoryPurchaseRequest $purchaseRequest)
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,decline',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->principalDecidePurchase($purchaseRequest, Auth::user(), $data['decision'], $data['remarks'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $data['decision'] === 'approve'
            ? 'Approved and forwarded to the General Manager.'
            : 'Purchase request declined.');
    }

    public function gmDecision(Request $request, InventoryPurchaseRequest $purchaseRequest)
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,decline',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // GM declines should carry a comment (your spec: "goes to decline list with comment").
        if ($data['decision'] === 'decline' && empty($data['remarks'])) {
            return back()->with('error', 'Please add a comment explaining the decline.');
        }

        try {
            $this->service->gmDecidePurchase($purchaseRequest, Auth::user(), $data['decision'], $data['remarks'] ?? null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $data['decision'] === 'approve'
            ? 'Purchase approved and added to the purchase list.'
            : 'Purchase declined.');
    }

    /** The purchase list — GM-approved items to be bought. */
    public function purchaseList()
    {
        $requests = InventoryPurchaseRequest::approved()
            ->with(['requester', 'principal', 'generalManager'])
            ->latest('gm_decided_at')
            ->paginate(20);

        return view('admin.inventory.purchases.list', compact('requests'));
    }

    /** The decline list — declined at either stage, with the comment. */
    public function declineList()
    {
        $requests = InventoryPurchaseRequest::declined()
            ->with(['requester', 'principal', 'generalManager'])
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.inventory.purchases.declined', compact('requests'));
    }

    public function cancel(InventoryPurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->requested_by !== Auth::id()) {
            abort(403);
        }
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'Only requests still awaiting the Principal can be withdrawn.');
        }
        $purchaseRequest->delete();

        return back()->with('success', 'Purchase request withdrawn.');
    }
}
