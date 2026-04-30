<?php

use App\Models\Authorized;
use App\Models\Unauthorized;
use App\Models\Registered;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component {
    public function with(): array
    {
        // Get trend data for the last 30 days
        $trends = Registered::selectRaw('DATE(target_date) as date, sum(add_quota) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn($item) => [
                'x' => $item->date,
                'y' => (int) $item->total,
            ])
            ->toArray();

        return [
            'stats' => [
                'total_authorized' => Authorized::count(),
                'total_unauthorized' => Unauthorized::count(),
                'total_quota' => (int) Registered::sum('add_quota'),
                'active_count' => Authorized::where('is_active', true)->count(),
                'inactive_count' => Authorized::where('is_active', false)->count(),
                'merah_count' => Authorized::where('group', 'merah')->count(),
                'biru_count' => Authorized::where('group', 'biru')->count(),
            ],
            'trends' => $trends,
        ];
    }
}; ?>

<x-slot name="title">Dashboard</x-slot>

<div class="flex h-full w-full flex-1 flex-col gap-6" x-data="{
    stats: @js($stats),
    trends: @js($trends),
    init() {
        if (typeof window.ApexCharts === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
            script.async = true;
            script.onload = () => this.initCharts();
            document.head.appendChild(script);
        } else {
            this.initCharts();
        }
    },
    initCharts() {
        // Group Distribution Chart (Donut)
        new ApexCharts(this.$refs.groupChart, {
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'inherit',
                animations: { enabled: true, easing: 'easeinout', speed: 800 }
            },
            series: [this.stats.merah_count, this.stats.biru_count],
            labels: ['Merah', 'Biru'],
            colors: ['#ef4444', '#3b82f6'],
            legend: { position: 'bottom', fontSize: '14px' },
            dataLabels: { enabled: true, dropShadow: { enabled: false } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: () => this.stats.total_authorized
                            }
                        }
                    }
                }
            },
            stroke: { show: false }
        }).render();

        // Status Distribution Chart (Bar)
        new ApexCharts(this.$refs.statusChart, {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            series: [{
                name: 'Users',
                data: [this.stats.active_count, this.stats.inactive_count]
            }],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '50%',
                    distributed: true,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#22c55e', '#a1a1aa'],
            xaxis: {
                categories: ['Active', 'Inactive'],
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: { show: false },
            legend: { show: false },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: { fontSize: '12px', colors: ['#304758'] }
            }
        }).render();

        // Trends Chart (Area)
        new ApexCharts(this.$refs.trendChart, {
            chart: {
                type: 'area',
                height: 350,
                fontFamily: 'inherit',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: [{
                name: 'Quota Added',
                data: this.trends
            }],
            xaxis: {
                type: 'datetime',
                labels: { datetimeUTC: false }
            },
            yaxis: { title: { text: 'Quota' } },
            colors: ['#8b5cf6'],
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
            grid: { borderColor: '#f1f1f1' }
        }).render();
    }
}">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <flux:heading size="xl" level="1">Dashboard Overview</flux:heading>
            <flux:subheading size="lg">Real-time statistics and usage trends for lunch management.</flux:subheading>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <flux:card class="relative flex flex-col gap-1 overflow-hidden">
            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <flux:icon name="users" variant="mini" />
                <flux:text size="sm" font="medium">Total Authorized</flux:text>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ number_format($stats['total_authorized']) }}</flux:heading>
                <flux:text size="xs" class="text-green-500 font-medium">Verified Users</flux:text>
            </div>
            <div class="absolute -right-4 -top-4 opacity-10">
                 <flux:icon name="users" size="xl" class="size-24" />
            </div>
        </flux:card>

        <flux:card class="relative flex flex-col gap-1 overflow-hidden">
            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <flux:icon name="exclamation-triangle" variant="mini" />
                <flux:text size="sm" font="medium">Unauthorized Attempts</flux:text>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ number_format($stats['total_unauthorized']) }}</flux:heading>
                <flux:text size="xs" class="text-red-500 font-medium">Recent Blocks</flux:text>
            </div>
             <div class="absolute -right-4 -top-4 opacity-10">
                 <flux:icon name="exclamation-triangle" size="xl" class="size-24" />
            </div>
        </flux:card>

        <flux:card class="relative flex flex-col gap-1 overflow-hidden">
            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <flux:icon name="bolt" variant="mini" />
                <flux:text size="sm" font="medium">Total Quota Distributed</flux:text>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl">{{ number_format($stats['total_quota']) }}</flux:heading>
                <flux:text size="xs" class="text-blue-500 font-medium">Units Added</flux:text>
            </div>
             <div class="absolute -right-4 -top-4 opacity-10">
                 <flux:icon name="bolt" size="xl" class="size-24" />
            </div>
        </flux:card>
    </div>

    {{-- Main Charts Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Group Distribution</flux:heading>
                <flux:text size="xs" class="text-zinc-400">Color-coded Access</flux:text>
            </div>
            <div class="flex-1" x-ref="groupChart"></div>
        </flux:card>

        <flux:card class="flex flex-col gap-4">
             <div class="flex items-center justify-between">
                <flux:heading size="lg">Access Status</flux:heading>
                <flux:text size="xs" class="text-zinc-400">Active vs Inactive</flux:text>
            </div>
            <div class="flex-1" x-ref="statusChart"></div>
        </flux:card>
    </div>

    {{-- Trends Section --}}
    <flux:card class="flex flex-col gap-4">
        <div class="flex items-center justify-between border-b border-zinc-100 pb-4 dark:border-zinc-800">
            <div>
                <flux:heading size="lg">Quota Addition History</flux:heading>
                <flux:subheading>Tracking the volume of quota additions over time.</flux:subheading>
            </div>
            {{-- <flux:button variant="ghost" icon="arrow-down-tray" size="sm">Export Data</flux:button> --}}
        </div>
        <div class="h-80 w-full" x-ref="trendChart"></div>
    </flux:card>
</div>
