<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Domain;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

class DomainObserver
{
    /**
     * Handle the Domain "created" event.
     */
    public function created(Domain $domain): void
    {
        $this->redis()?->set($domain->domain, 'lua.sha');
    }

    /**
     * Handle the Domain "updated" event.
     */
    public function updated(Domain $domain): void
    {
        $redis = $this->redis();

        if (! $redis) {
            return;
        }

        // delete the old domain
        $redis->del($domain->getOriginal('domain'));

        // set the new domain
        $redis->set($domain->domain, 'lua.sha');
    }

    /**
     * Handle the Domain "deleted" event.
     */
    public function deleted(Domain $domain): void
    {
        $this->redis()?->del($domain->domain);
    }

    /**
     * Connected only when announcing is on, so an install without Redis never
     * tries to reach it.
     */
    private function redis(): ?Connection
    {
        return config('lua.announce_domains') ? Redis::connection('default') : null;
    }
}
