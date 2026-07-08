<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryItemRequest;
use App\Models\InventoryPurchaseRequest;
use App\Services\InventoryRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Teacher-portal mirror of the admin "My Requests" page — same shared service, so the
 * two submission paths can't diverge.
 */
class InventoryRequestController extends Controller
{
    public function __construct(private InventoryRequestService $service) {}

    public function index()
    {
        $employee = $this->currentEmployee();

        return view('teacher.inventory-requests.index', [
            'itemRequests' => $employee
                ? InventoryItemRequest::where('requester_employee_id', $employee->id)->with(['item', 'decider'])->latest()->get()
                : collect(),
            'purchaseRequests' => InventoryPurchaseRequest::where('requested_by', Auth::id())->with(['principal', 'generalManager'])->latest()->get(),
            'items' => InventoryItem::where('is_active', true)->orderBy('name')->get(),
            'categories' => InventoryCategory::orderBy('name')->get(),
            'hasEmployee' => (bool) $employee,
        ]);
    }

    public function storeItem(Request $request)
    {
        $employee = $this->currentEmployee();
        if (! $employee) {
            return back()->with('error', 'No employee record is linked to your account.');
        }

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'required|string|max:1000',
        ]);

        $this->service->submitItemRequest($employee, $validated);

        return back()->with('success', 'Item request submitted for approval.');
    }

    public function storePurchase(Request $request)
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

    public function cancelPurchase(InventoryPurchaseRequest $purchaseRequest)
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

    private function currentEmployee(): ?Employee
    {
        return Employee::withoutGlobalScopes()->where('user_id', Auth::id())->first();
    }
}
