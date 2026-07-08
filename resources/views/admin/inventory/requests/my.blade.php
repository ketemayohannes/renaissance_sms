<x-admin-layout>
    <div class="space-y-8">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Inventory', 'url' => route('admin.inventory.dashboard')],
                ['label' => 'My Requests', 'url' => '#']
            ]" />
            <h1 class="text-4xl font-black text-slate-900 dark:text-slate-100 tracking-tight mt-2">My Requests</h1>
            <p class="text-slate-500 dark:text-slate-400 font-semibold mt-1">Request items from stock, or request a purchase.</p>
        </div>

        @include('partials.inventory.my-requests', [
            'itemStoreRouteName' => 'admin.inventory.my-requests.item.store',
            'itemCancelRouteName' => 'admin.inventory.my-requests.item.cancel',
            'purchaseStoreRouteName' => 'admin.inventory.my-requests.purchase.store',
            'purchaseCancelRouteName' => 'admin.inventory.my-requests.purchase.cancel',
        ])
    </div>
</x-admin-layout>
