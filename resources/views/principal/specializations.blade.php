@extends('layouts.app')

@section('title', 'Specializations')
@section('subtitle', 'Manage SHS specializations per track')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="flex justify-end mb-4">
    <button type="button" onclick="openAddModal()"
        class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
        <i class="bi bi-plus-lg"></i>Add Specialization
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500">
            <tr>
                <th class="text-left px-6 py-3">Specialization</th>
                <th class="text-left px-6 py-3">Code</th>
                <th class="text-left px-6 py-3">Track</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($specializations as $spec)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $spec->name }}</td>
                <td class="px-6 py-3">
                    <span class="bg-purple-100 text-purple-700 text-xs font-semibold px-2 py-1 rounded">
                        {{ $spec->code }}
                    </span>
                </td>
                <td class="px-6 py-3 text-gray-600">{{ $spec->track->name ?? '—' }}</td>
                <td class="px-6 py-3 text-right space-x-2">
                    <button type="button"
                        onclick='openEditModal(@json($spec))'
                        class="text-blue-600 hover:underline text-sm"><i class="bi bi-pencil-square"></i>Edit</button>

                    <form method="POST"
                          action="{{ route('principal.specializations.destroy', $spec->id) }}"
                          class="inline"
                          onsubmit="return confirm('Delete this specialization?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm"><i class="bi bi-trash"></i>Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-6 text-center text-gray-400">
                    No specializations yet. Add tracks first.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- MODAL --}}
<div id="specModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Add Specialization</h3>
            <button type="button" onclick="closeModal()"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form id="specForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div>
                <label class="block text-sm text-gray-600 mb-1">Track</label>
                <select name="track_id" id="specTrack" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— Select Track —</option>
                    @foreach($tracks as $track)
                        <option value="{{ $track->id }}">{{ $track->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Specialization Name</label>
                <input type="text" name="name" id="specName" required
                       placeholder="e.g. HUMSS"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Code</label>
                <input type="text" name="code" id="specCode" required
                       placeholder="e.g. HUMSS"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                <button type="submit"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                    Save Specialization
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const SPEC_STORE_URL = "{{ route('principal.specializations.store') }}";
</script>
<script src="{{ asset('js/principal/specializations.js') }}"></script>
@endpush

@endsection