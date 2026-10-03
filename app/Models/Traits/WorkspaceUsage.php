<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Link;
use App\Models\LinkStat;
use App\Models\Plan;
use Carbon\Carbon;

trait WorkspaceUsage
{
    public function usage()
    {
        $billingCycleStart = $this->billing_cycle_start;
        $start = Carbon::now()->day >= $billingCycleStart ? Carbon::now()->startOfMonth()->day($billingCycleStart) : Carbon::now()->subMonth()->startOfMonth()->day($billingCycleStart);
        $end = (clone $start)->addMonth()->subDay();

        $usedLinks = Link::where('workspace_id', $this->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $usedEvents = LinkStat::where('workspace_id', $this->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $linksChartData = Link::where('workspace_id', $this->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $eventsChartData = LinkStat::where('workspace_id', $this->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'plan' => [
                'name' => $this->plan->name,
                'access_level' => $this->plan->access_level,
                'next_tier' => Plan::where('access_level', '>', $this->plan->access_level)
                    ->where('is_private', false)
                    ->orderBy('access_level', 'asc')
                    ->select('name')
                    ->first(),
            ],
            'current_billing_cycle' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'current_billing_cycle_formatted' => $start->format('M j, Y').' - '.$end->format('M j, Y'),
            'next_reset' => $end->format('M j, Y'),
            'links' => [
                ...$this->allowance($usedLinks, $this->plan->max_links),
                'chart' => [
                    'total' => $linksChartData->sum('count'),
                    'chart' => [
                        'data' => $linksChartData->pluck('count')->toArray(),
                        'label' => 'Links',
                        'labels' => $linksChartData->pluck('date')->map(function ($date) {
                            return Carbon::parse($date)->format('d, Y');
                        })->toArray(),
                    ],
                ],
            ],

            'events' => [
                ...$this->allowance($usedEvents, $this->plan->max_events),
                'chart' => [
                    'total' => $eventsChartData->sum('count'),
                    'chart' => [
                        'data' => $eventsChartData->pluck('count')->toArray(),
                        'label' => 'Events',
                        'labels' => $eventsChartData->pluck('date')->map(function ($date) {
                            return Carbon::parse($date)->format('d, Y');
                        })->toArray(),
                    ],
                ],
            ],

            'domains' => $this->allowance($this->domains->count(), $this->plan->max_domains),
            'tags' => $this->allowance($this->tags->count(), $this->plan->max_tags),
            'users' => $this->allowance($this->users->count(), $this->plan->max_users),
        ];
    }

    /**
     * A null limit is unlimited: never reached, and nothing to measure against.
     *
     * @return array{used: int, limit: int|null, percent: int|float, remaining: int|null, reached_limit: bool}
     */
    private function allowance(int $used, ?int $limit): array
    {
        if ($limit === null) {
            return [
                'used' => $used,
                'limit' => null,
                'percent' => 0,
                'remaining' => null,
                'reached_limit' => false,
            ];
        }

        return [
            'used' => $used,
            'limit' => $limit,
            'percent' => $limit === 0 ? 0 : round(($used / $limit) * 100),
            'remaining' => $limit - $used,
            'reached_limit' => $used >= $limit,
        ];
    }
}
