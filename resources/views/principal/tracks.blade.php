@extends('layouts.app')

@section('title', 'Tracks')
@section('subtitle', 'Manage SHS tracks')

@section('content')

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm mb-0">
    {{-- Header + Add Button --}}
    <div class="flex items-center justify-between px-6 py-4 border-b">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">All Tracks</h2>
            <p class="text-xs text-gray-400">{{ $tracks->count() }} total tracks</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 whitespace-nowrap">
            <i class="bi bi-plus-lg"></i> Add Track
        </button>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
            <tr>
                <th class="text-left px-6 py-3">Track Name</th>
                <th class="text-left px-6 py-3">Code</th>
                <th class="text-left px-6 py-3">Specializations</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tracks as $track)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 font-medium text-gray-800">{{ $track->name }}</td>
                <td class="px-6 py-3">
                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded">
                        {{ $track->code }}
                    </span>
                </td>
                <td class="px-6 py-3 text-gray-600">
                    @if($track->specializations->count() > 0)
                        {{ $track->specializations->pluck('name')->join(', ') }}
                    @else
                        <span class="text-gray-400 text-xs">No specializations yet</span>
                    @endif
                </td>
                <td class="px-6 py-3 text-right space-x-2">
                    <button type="button"
                        onclick='openEditModal(@json($track))'
                        class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-medium border border-blue-200 rounded px-2 py-1 hover:bg-blue-50">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <form method="POST"
                          action="{{ route('principal.tracks.destroy', $track->id) }}"
                          class="inline"
                          onsubmit="return confirm('Delete this track? All specializations under it will also be deleted.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-xs font-medium border border-red-200 rounded px-2 py-1 hover:bg-red-50">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                    <i class="bi bi-diagram-3 text-2xl block mb-2"></i>
                    No tracks yet. Add Academic Track and TechPro Track to get started.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ADD / EDIT MODAL --}}
<div id="trackModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">

        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-semibold text-gray-800">Add Track</h3>
            <button type="button" onclick="closeModal()"
                    class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <form id="trackForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div>
                <label class="block text-sm text-gray-600 mb-1">Track Name</label>
                <input type="text" name="name" id="trackName" required
                       placeholder="e.g. Academic Track"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Code</label>
                <input type="text" name="code" id="trackCode" required
                       placeholder="e.g. ACAD"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <p class="text-xs text-gray-400 mt-1">Short code for the track (auto-uppercased)</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeModal()"
                        class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                <button type="submit"
                        class="bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-800">
                    Save Track
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const TRACK_STORE_URL = "{{ route('principal.tracks.store') }}";
</script>
<script src="{{ asset('js/principal/tracks.js') }}"></script>
@endpush

@endsection