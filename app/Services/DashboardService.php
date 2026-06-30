<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\Authorized;
use App\Models\QuotaSchedule;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', 300, fn () => $this->fetchStats());
    }

    public function getTrends(?string $startDate, ?string $endDate): array
    {
        return Cache::remember(
            'dashboard.trends.'.md5($startDate.$endDate),
            300,
            fn () => $this->fetchTrends($startDate, $endDate)
        );
    }

    public function getTodayStats(): array
    {
        $dateKey = now()->toDateString();

        return Cache::remember("dashboard.today.{$dateKey}", 60, fn () => $this->fetchTodayStats());
    }

    public function getCategoryStats(): array
    {
        return Cache::remember('dashboard.categories', 300, fn () => $this->fetchCategoryStats());
    }

    protected function fetchStats(): array
    {
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

        $totalQuota = QuotaSchedule::query()
            ->currentMonth()
            ->sum('add_quota');

        return [
            'total_authorized' => (int) $authorizedStats->total,
            'total_quota' => (int) $totalQuota,
            'active_count' => (int) $authorizedStats->active_count,
            'inactive_count' => (int) $authorizedStats->inactive_count,
            'merah_count' => (int) $authorizedStats->merah_count,
            'biru_count' => (int) $authorizedStats->biru_count,
        ];
    }

    protected function fetchTrends(?string $startDate, ?string $endDate): array
    {
        return QuotaSchedule::query()
            ->selectRaw('target_date as date, SUM(add_quota) as total')
            ->whereNotNull('target_date')
            ->inDateRange($startDate, $endDate)
            ->groupBy('target_date')
            ->orderBy('target_date', 'asc')
            ->get()
            ->map(fn ($item) => [
                'x' => strtotime((string) $item->date) * 1000,
                'y' => (int) $item->total,
            ])
            ->toArray();
    }

    protected function fetchTodayStats(): array
    {
        $stats = AccessLog::query()
            ->today()
            ->selectRaw(
                'COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as failed_count',
                ['authorized', 'authorized']
            )
            ->first();

        return [
            'total_access_logs' => (int) ($stats->total ?? 0),
            'access_success_count' => (int) ($stats->success_count ?? 0),
            'access_failed_count' => (int) ($stats->failed_count ?? 0),
        ];
    }

    protected function fetchCategoryStats(): array
    {
        $stats = AccessLog::query()
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cat_authorized,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cat_wrong_group,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cat_no_quota,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cat_inactive,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cat_not_registered',
                ['authorized', 'wrong group', 'no quota', 'inactive', 'not registered']
            )
            ->first();

        return [
            'category_authorized' => (int) ($stats->cat_authorized ?? 0),
            'category_wrong_group' => (int) ($stats->cat_wrong_group ?? 0),
            'category_no_quota' => (int) ($stats->cat_no_quota ?? 0),
            'category_inactive' => (int) ($stats->cat_inactive ?? 0),
            'category_not_registered' => (int) ($stats->cat_not_registered ?? 0),
        ];
    }
}
