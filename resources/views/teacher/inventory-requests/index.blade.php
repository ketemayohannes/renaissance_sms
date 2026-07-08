<x-teacher-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 font-heading">Inventory Requests</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">Request items from the store, or request a purchase of something new.</p>
        </div>

        @include('partials.inventory.my-requests', [
            'itemStoreRouteName' => 'teacher.inventory-requests.item.store',
            'itemCancelRouteName' => 'teacher.inventory-requests.item.cancel',
            'purchaseStoreRouteName' => 'teacher.inventory-requests.purchase.store',
            'purchaseCancelRouteName' => 'teacher.inventory-requests.purchase.cancel',
        ])
    </div>
</x-teacher-layout>
