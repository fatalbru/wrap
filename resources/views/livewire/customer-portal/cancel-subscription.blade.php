<flux:modal name="cancel-subscription" @close="closeModal">
    @if(filled($subscription))
        <div class="space-y-4 sm:min-w-sm">
            <div>
                <flux:heading size="lg">
                    {{$subscription->price->name}}
                </flux:heading>
                <flux:subheading>
                    {{$subscription->ksuid}}
                </flux:subheading>
            </div>
            @if($subscription->trial)
                <flux:callout
                    variant="secondary"
                    :text="__('Your trial ends :date', ['date' => $subscription->trial_ended_at->toDateTimeString() ])"
                />
            @endif
            <flux:text>
                {{__('Next payment scheduled for :date', ['date' => $subscription->next_payment_at->toDateTimeString() ])}}
            </flux:text>
            <div>
                <flux:button variant="danger" wire:click="cancelSubscription" wire:loading.attr="disabled" wire:target="cancelSubscription">
                    {{__('Cancel subscription')}}
                </flux:button>
            </div>
        </div>
    @endif
</flux:modal>
