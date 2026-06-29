<?php

use App\Models\AccessLog;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function with(): array
    {
        $this->authorize('viewAny', AccessLog::class);

        return [
            'accessLogs' => AccessLog::query()
                ->with('authorized.user')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('uuid', 'ilike', $this->search . '%')
                            ->orWhere('group', 'ilike', $this->search . '%')
                            ->orWhere('status', 'ilike', $this->search . '%')
                            ->orWhereHas('authorized.user', function ($userQuery) {
                                $userQuery->where('first_name', 'ilike', $this->search . '%')
                                    ->orWhere('last_name', 'ilike', $this->search . '%')
                                    ->orWhere('nik', 'ilike', $this->search . '%');
                            });
                    });
                })
                ->orderByDesc('scanned_at')
                ->paginate(15),
        ];
    }
}; ?>

<x-slot name="title">Access Logs</x-slot>

<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- Page Header --}}
    <div class="flex items-start justify-between">
        <div>
            <flux:heading size="xl" level="1">Access Logs</flux:heading>
            <flux:subheading size="lg">View scan and access history for all authorized users.</flux:subheading>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live="search"
            icon="magnifying-glass"
            placeholder="Search by UUID, group, status, or user..."
            class="w-full sm:max-w-xs"
        />
    </div>

    {{-- Table Card --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">UUID</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Full Name</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Group</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Scanned At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($accessLogs as $log)
                        <tr class="transition-colors duration-150 hover:bg-hover/20 dark:hover:bg-hover/30" wire:key="access-log-{{ $log->id }}">
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $log->uuid }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                    {{ $log->authorized?->user?->first_name ?? '' }} {{ $log->authorized?->user?->last_name ?? '' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <flux:badge size="sm" :color="$log->group === 'merah' ? 'red' : 'blue'" inset="top bottom" class="w-20 justify-center">
                                    {{ ucfirst($log->group) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <flux:badge size="sm" :color="$log->status === 'success' ? 'green' : 'red'" inset="top bottom" class="w-24 justify-center">
                                    {{ ucfirst($log->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $log->scanned_at }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-400 dark:text-zinc-500">
                                No access logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($accessLogs->hasPages())
            <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800">
                {{ $accessLogs->links('vendor.pagination.bordered-case') }}
            </div>
        @endif
    </div>

</div>
