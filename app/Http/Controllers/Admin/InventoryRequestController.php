<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Models\InventoryPurchaseRequest;
use App\Services\InventoryRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryRequestController extends Controller
{
    public function __construct(private InventoryRequestService $service) {}

    /**
     * Shared "My Requests" page for admin-role requesters (Principal, VP, Supervisor…).
     * Teachers get the mirror of this in the teacher portal.
     */
    public function myRequests()
    {
        $employee = $this->currentEmployee();

        $itemRequests = $employee
            ? InventoryItemRequest::where('requester_employee_id', $employee->id)->with(['item', 'decider'])->latest()->get()
            : collect();

        $purchaseRequests = InventoryPurchaseRequest::where('requested_by', Auth::id())->with('principal', 'generalManager')->latest()->get();

        return view('admin.inventory.requests.my', [
            'itemRequests' => $itemRequests,
            'purchaseRequests' => $purchaseRequests,
            'items' => InventoryItem::where('is_active', true)->orderBy('name')->get(),
            'categories' => \App\Models\InventoryCategory::orderBy('name')->get(),
            'hasEmployee' => (bool) $employee,
        ]);
    }

    public function storeItem(Request $request)
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return back()->with('error', 'No employee record is linked to your account, so you cannot request items.');
        }

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:1000',
        ]);

        $this->service->submitItemRequest($employee, $validated);

        return back()->with('success', 'Item request submitted for approval.');
    }

    public function cancelItem(InventoryItemRequest $itemRequest)
    {
        $employee = $this->currentEmployee();
        if (! $employee || $itemRequest->requester_employee_id !== $employee->id) {
            abort(403);
        }
        if ($itemRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be withdrawn.');
        }
        $itemRequest->delete();

        return back()->with('success', 'Request withdrawn.');
    }

    // ─────────────── Principal approval queue ───────────────

    public function index(Request $request)
    {
        $requests = InventoryItemRequest::with(['requester', 'item', 'decider'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'fulfilled', 'rejected')")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.requests.index', [
            'requests' => $requests,
            'pendingCount' => InventoryItemRequest::pending()->count(),
        ]);
    }

    public function approve(Request $request, InventoryItemRequest $itemRequest)
    {
        $request->validate(['decision_remarks' => 'nullable|string|max:1000']);
        try {
            $this->service->approveItemRequest($itemRequest, Auth::user(), $request->decision_remarks);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request approved. The store keeper has been notified.');
    }

    public function reject(Request $request, InventoryItemRequest $itemRequest)
    {
        $request->validate(['decision_remarks' => 'nullable|string|max:1000']);
        try {
            $this->service->rejectItemRequest($itemRequest, Auth::user(), $request->decision_remarks);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request rejected.');
    }

    // ─────────────── Inventory Manager fulfilment ───────────────

    public function fulfilment()
    {
        $requests = InventoryItemRequest::awaitingFulfilment()
            ->with(['requester', 'item'])
            ->latest('decided_at')
            ->get();

        return view('admin.inventory.requests.fulfilment', compact('requests'));
    }

    public function fulfil(InventoryItemRequest $itemRequest)
    {
        try {
            $this->service->fulfilItemRequest($itemRequest, Auth::user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Item handed over and stock updated.');
    }

    private function currentEmployee(): ?Employee
    {
        return Employee::withoutGlobalScopes()->where('user_id', Auth::id())->first();
    }
}
