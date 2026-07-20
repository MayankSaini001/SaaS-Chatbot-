@extends('layouts.app')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">All Tenants</h1>

    <div class="flex items-center gap-4">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
            </svg>

            <input
                type="text"
                id="tenantSearch"
                placeholder="Search tenant..."
                class="pl-10 pr-4 py-2 w-80 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
            >
        </div>

        <span class="text-sm text-gray-500">
            Total: {{ $tenants->count() }}
        </span>
    </div>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="text-left px-6 py-3 text-gray-600">Name</th>
                <th class="text-left px-6 py-3 text-gray-600">Email</th>
                <th class="text-left px-6 py-3 text-gray-600">Plan</th>
                <th class="text-left px-6 py-3 text-gray-600">Conversations</th>
                <th class="text-left px-6 py-3 text-gray-600">Status</th>
                <th class="text-left px-6 py-3 text-gray-600">Joined</th>
                <th class="text-left px-6 py-3 text-gray-600">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $tenant)
            <tr class="border-b hover:bg-gray-50 tenant-row">
                <td class="px-6 py-4">
                    <a href="{{ route('admin.tenants.show', $tenant) }}"
                       class="tenant-name font-medium text-indigo-600 hover:underline">
                        {{ $tenant->name }}
                    </a>
                </td>
                <td class="px-6 py-4 text-gray-600 tenant-email">
					{{ $tenant->email }}
				</td>
                <td class="px-6 py-4">
                    <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs font-medium">
                        {{ ucfirst($tenant->plan) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $tenant->conversations_count }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500 text-xs">
                    {{ $tenant->created_at->format('d M Y') }}
                </td>
                <td class="px-6 py-4">
                    <form method="POST"
                          action="{{ route('admin.tenants.toggle', $tenant) }}">
                        @csrf
                        <button class="text-xs px-3 py-1 rounded border
                            {{ $tenant->is_active
                                ? 'border-red-300 text-red-600 hover:bg-red-50'
                                : 'border-green-300 text-green-600 hover:bg-green-50' }}">
                            {{ $tenant->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                    No tenants found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    const search = document.getElementById('tenantSearch');

    if (!search) return;

    search.addEventListener('keyup', function () {

        let value = this.value.toLowerCase().trim();

        document.querySelectorAll('.tenant-row').forEach(function (row) {

            let name = row.querySelector('.tenant-name').textContent.toLowerCase();
            let email = row.querySelector('.tenant-email').textContent.toLowerCase();

            row.style.display =
                (name.includes(value) || email.includes(value))
                    ? ''
                    : 'none';

        });

    });

});
</script>