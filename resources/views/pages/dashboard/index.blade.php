<?php

use App\Models\Authorized;
use App\Models\QuotaSchedule;
use App\Models\AccessLog;
use Livewire\Component;

new class extends Component {
    public function with(): array
    {
        // Get trend data for the last 30 days
        $trends = QuotaSchedule::selectRaw('DATE(target_date) as date, SUM(add_quota) as total')
            ->whereNotNull('target_date')
            ->where('target_date', '>=', now()->subDays(30)->toDateString())
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(fn ($item) => [
                'x' => strtotime($item->date) * 1000,
                'y' => (int) $item->total,
            ])
            ->toArray();

        // Single aggregated query instead of 5 separate COUNT queries
        $authorizedStats = Authorized::query()
            ->selectRaw(
                'COUNT(*) as total,
                SUM(CASE WHEN is_active = true THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN is_active = false THEN 1 ELSE 0 END) as inactive_count,
                SUM(CASE WHEN "group" = ? THEN 1 ELSE 0 END) as merah_count,
                SUM(CASE WHEN "group" = ? THEN 1 ELSE 0 END) as biru_count',
                ['merah', 'biru']
            )
            ->first();

        // Get access log stats
        $accessLogStats = AccessLog::query()
            ->selectRaw(
                'COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed_count',
                ['success', 'failed']
            )
            ->first();

        return [
            'stats' => [
                'total_authorized' => (int) $authorizedStats->total,
                'total_quota' => (int) QuotaSchedule::sum('add_quota'),
                'active_count' => (int) $authorizedStats->active_count,
                'inactive_count' => (int) $authorizedStats->inactive_count,
                'merah_count' => (int) $authorizedStats->merah_count,
                'biru_count' => (int) $authorizedStats->biru_count,
                'total_access_logs' => (int) $accessLogStats->total,
                'access_success_count' => (int) $accessLogStats->success_count,
                'access_failed_count' => (int) $accessLogStats->failed_count,
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
            colors: ['#EF4444', '#3B82F6'],
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
                    borderRadius: 12,
                    columnWidth: '45%',
                    distributed: true,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#22C55E', '#71717A'],
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
                style: { fontSize: '12px', colors: ['#5b5856'] }
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
            colors: ['#3B82F6'],
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            markers: { size: 5, strokeWidth: 3, hover: { size: 7 } },
            grid: { borderColor: '#e2e8f0' }
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <flux:card class="group relative flex flex-col gap-2 overflow-hidden p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <flux:icon name="users" variant="mini" />
                </div>
                <flux:text size="sm" font="medium">Total Authorized</flux:text>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl" class="group-hover:text-blue-500 transition-colors">{{ number_format($stats['total_authorized']) }}</flux:heading>
                <flux:text size="xs" class="text-blue-500 font-semibold bg-blue-500/10 px-2 py-0.5 rounded-full">Verified</flux:text>
            </div>
            <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity">
                 <flux:icon name="users" size="xl" class="size-32" />
            </div>
        </flux:card>

        <flux:card class="group relative flex flex-col gap-2 overflow-hidden p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <div class="p-2 rounded-lg bg-violet-500/10 text-violet-500 group-hover:bg-violet-500 group-hover:text-white transition-colors">
                    <flux:icon name="document-text" variant="mini" />
                </div>
                <flux:text size="sm" font="medium">Total Access Logs</flux:text>
            </div>
            <div class="flex items-baseline gap-2 flex-wrap">
                <flux:heading size="xl" class="group-hover:text-violet-500 transition-colors">{{ number_format($stats['total_access_logs']) }}</flux:heading>
                <flux:text size="xs" class="text-green-600 font-semibold bg-green-100 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-full">{{ number_format($stats['access_success_count']) }} Success</flux:text>
                <flux:text size="xs" class="text-red-600 font-semibold bg-red-100 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded-full">{{ number_format($stats['access_failed_count']) }} Failed</flux:text>
            </div>
             <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity">
                 <flux:icon name="document-text" size="xl" class="size-32" />
            </div>
        </flux:card>

        <flux:card class="group relative flex flex-col gap-2 overflow-hidden p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer">
            <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                <div class="p-2 rounded-lg bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                    <flux:icon name="bolt" variant="mini" />
                </div>
                <flux:text size="sm" font="medium">Quota Distributed</flux:text>
            </div>
            <div class="flex items-baseline gap-2">
                <flux:heading size="xl" class="group-hover:text-amber-500 transition-colors">{{ number_format($stats['total_quota']) }}</flux:heading>
                <flux:text size="xs" class="text-amber-500 font-semibold bg-amber-500/10 px-2 py-0.5 rounded-full">Units</flux:text>
            </div>
             <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity">
                 <flux:icon name="bolt" size="xl" class="size-32" />
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
