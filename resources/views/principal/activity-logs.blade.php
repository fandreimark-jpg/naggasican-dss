@extends('layouts.app')

@section('title', 'Activity Logs')
@section('subtitle', 'System activity history')

@section('content')

<div class="bg-white rounded-lg shadow-sm">
    <div class="px-5 py-3 border-b">
        <h3 class="font-semibold text-gray-800 text-sm">Recent Activity</h3>
        <p class="text-xs text-gray-500">All system actions recorded</p>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs">
            <tr>
                <th class="text-left px-5 py-2">Date & Time</th>
                <th class="text-left px-3 py-2">User</th>
                <th class="text-left px-3 py-2">Role</th>
                <th class="text-left px-3 py-2">Action</th>
                <th class="text-left px-3 py-2">Description</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($logs as $log)
            @php
                $actionColors = [
                    'login'         => 'bg-blue-100 text-blue-700',
                    'logout'        => 'bg-gray-100 text-gray-600',
                    'encode_grades' => 'bg-green-100 text-green-700',
                    'submit_report' => 'bg-purple-100 text-purple-700',
                    'create_section'=> 'bg-yellow-100 text-yellow-700',
                    'add_student'   => 'bg-teal-100 text-teal-700',
                    'delete_student'=> 'bg-red-100 text-red-700',
                    'create_user'   => 'bg-indigo-100 text-indigo-700',
                ];
                $actionColor = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2 text-xs text-gray-500">
                    {{ $log->created_at->format('M d, Y h:i A') }}
                </td>
                <td class="px-3 py-2 font-medium text-gray-800 text-xs">
                    {{ $log->user->name ?? 'Unknown' }}
                </td>
                <td class="px-3 py-2 text-xs text-gray-500 capitalize">
                    {{ $log->user->role ?? '—' }}
                </td>
                <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $actionColor }}">
                        {{ str_replace('_', ' ', ucfirst($log->action)) }}
                    </span>
                </td>
                <td class="px-3 py-2 text-xs text-gray-600">
                    {{ $log->description }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-6 text-center text-gray-400 text-xs">
                    No activity logs yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t flex flex-col items-center gap-2 text-sm text-gray-500">
        <div class="flex items-center gap-1">
            @if($logs->onFirstPage())
                <span class="px-3 py-1 rounded border text-gray-300 cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}"
                   class="px-3 py-1 rounded border hover:bg-gray-50 text-gray-600">← Prev</a>
            @endif
            <span class="px-3 py-1 rounded border bg-blue-700 text-white font-medium">
                {{ $logs->currentPage() }}
            </span>
            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}"
                   class="px-3 py-1 rounded border hover:bg-gray-50 text-gray-600">Next →</a>
            @else
                <span class="px-3 py-1 rounded border text-gray-300 cursor-not-allowed">Next →</span>
            @endif
        </div>
        <span class="text-xs">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} Logs</span>
    </div>
    @endif
</div>

@endsection