<?php

namespace App\Console\Commands\Subscriptions;

use App\Events\Subscriptions\TrialEnded;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class NotifyCompletedTrials extends Command
{
    protected $signature = 'subscriptions:notify-completed-trials';

    protected $description = 'Dispatches webhooks on ended trials';

    public function handle()
    {
        Subscription::trialEnded()
            ->whereDoesntHave('webhookLogs', function (Builder $builder): void {
                $builder->whereEventName(config('wrap.webhook_event_name')[TrialEnded::class]);
            })
            ->lazy()
            ->each(function (Subscription $subscription): void {
                event(new TrialEnded($subscription));
            });
    }
}
