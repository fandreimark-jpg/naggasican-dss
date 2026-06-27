@extends('layouts.app')

@section('title', 'User Management')
@section('subtitle', 'Manage user accounts')

@section('content')

<div class="flex justify-end mb-4">
    <button type="button" onclick="openAddModal()"
        class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
        + Add User
    </button>
</div>

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="text-left px-6 py-3">Last Name</th>
                <th class="text-left px-6 py-3">First Name</th>
                <th class="text-left px-6 py-3">Middle Name</th>
                <th class="text-left px-6 py-3">Email</th>
                <th class="text-left px-6 py-3">Role</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $user->last_name ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-800">{{ $user->first_name ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-600">{{ $user->middle_name ?? '—' }}</td>
                <td class="px-6 py-3 text-gray-600">{{ $user->email }}</td>
                <td class="px-6 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $user->role === 'principal' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-6 py-3 text-right space-x-2">
                    <button type="button"
                        onclick='openUserEditModal(@json($user))'
                        class="text-blue-600 hover:underline"><i class="bi bi-pencil-square"></i> Edit</button>

                    {{-- Hindi pwedeng i-delete ang sariling account --}}
                    @if($user->id !== auth()->id())
                    <form method="POST"
                        action="{{ route('principal.users.destroy', $user->id) }}"
                        class="inline"
                        onsubmit="return confirm('Remove this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline"><i class="bi bi-trash"></i>Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-6 text-center text-gray-400">No users yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ADD / EDIT MODAL --}}
<div id="userModal"
     class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div id="userModalBox" class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Add User</h3>
            <button type="button" onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 text-sm p-3 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form id="userForm" method="POST"
              data-store-url="{{ route('principal.users.store') }}"
              class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="field_last_name" required
                           value="{{ old('last_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">First Name</label>
                    <input type="text" name="first_name" id="field_first_name" required
                           value="{{ old('first_name') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Middle Name</label>
                <input type="text" name="middle_name" id="field_middle_name"
                       value="{{ old('middle_name') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Username</label>
                <div class="flex items-center border rounded-lg overflow-hidden">
                    <input type="text" name="username" id="field_username" required
                           value="{{ old('username') }}"
                           placeholder="e.g. juan.delacruz"
                           class="flex-1 px-3 py-2 text-sm outline-none">
                    <span class="bg-gray-100 px-3 py-2 text-sm text-gray-500 border-l">
                        @naggasican.edu.ph
                    </span>
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Role</label>
                <select name="role" id="field_role" required
                        class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="adviser">Adviser</option>
                    <option value="principal">Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Password
                    <span class="text-gray-400 text-xs">(minimum 8 characters)</span>
                </label>
                <input type="password" name="password" id="field_password"
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       placeholder="Enter password">
                <p id="passwordNote" class="text-xs text-gray-400 mt-1 hidden">
                    Leave blank to keep current password
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeUserModal()"
                        class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                <button type="submit" id="submitBtn"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

@endsection