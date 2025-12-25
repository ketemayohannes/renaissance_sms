<x-admin-layout>
    <x-slot name="header">Create New Role</x-slot>

    <div class="space-y-6">
        <div class="card overflow-hidden">
            <div class="p-6">
                    <form action="{{ route('admin.roles.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($permissions as $group => $perms)
                                    <div class="border rounded p-4">
                                        <h3 class="font-bold mb-2 capitalize">{{ $group }}</h3>
                                        @foreach($perms as $permission)
                                            <div class="flex items-center mb-1">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="perm_{{ $permission->id }}" class="ml-2 block text-sm text-gray-900">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <a href="{{ route('admin.roles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</a>
                            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Create Role</button>
                        </div>
                    </form>
                </div>
        </div>
    </div>
</x-admin-layout>
