@use(App\PaymentMethod)
@use(App\PaymentVendor)
@use(App\ProductType;use Illuminate\Support\Number)
<div class="sm:max-w-5xl w-full mx-auto p-6 sm:p-8 flex flex-col">
    <livewire:customer-portal.cancel-subscription/>
    <div class="grid grid-cols-1 overflow-hidden rounded-2xl shadow-sm grow">
        <div class="space-y-4 sm:space-y-6 p-4 sm:p-8 bg-zinc-100">
            <x-application-logo class="w-40"/>
            <flux:heading size="lg">
                {{$customer->name}}
            </flux:heading>
            <flux:subheading>
                {{$customer->email}}
            </flux:subheading>
            <flux:button icon="arrow-left" :href="$backUrl" size="sm">
                {{__('Return to :site', ['site' => config('app.name')])}}
            </flux:button>
        </div>
        <div class="p-4 sm:p-8 border-b border-zinc-100 space-y-4 sm:space-y-8">
            <flux:heading size="lg">
                {{__('Subscriptions')}}
            </flux:heading>
            <div class="sm:hidden space-y-2">
                @forelse($this->subscriptions as $subscription)
                    <flux:card size="sm">
                        <div class="space-y-2">
                            <div>
                                <flux:heading>
                                    {{$subscription->price->name}}
                                </flux:heading>
                                <flux:subheading>
                                    {{$subscription->ksuid}}
                                </flux:subheading>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:badge size="sm" :color="$subscription->status->getBadgeColor()">
                                    {{$subscription->status->getLabel()}}
                                </flux:badge>
                                @if($subscription->trial)
                                    <flux:text size="sm" variant="subtle">
                                        {{__('Trial until :date', [ 'date' => $subscription->trial_ended_at->toDateString()])}}
                                    </flux:text>
                                @endif
                            </div>
                            <flux:text>
                                $ {{Number::format($subscription->price->price,2)}}
                                / {{$subscription->price->frequency->getLabel()}}
                            </flux:text>
                            <flux:text>
                                <b>{{__('Payment Method')}}:</b>
                                {{$subscription->payment_method?->getLabel()}}
                            </flux:text>
                            <flux:text>
                                <b>{{__('Next Payment')}}:</b>
                                {{$subscription->next_payment_at?->toDateTimeString()}}
                            </flux:text>
                            @if($subscription->cancelable)
                                <flux:button size="sm" wire:click="startCancellation({{$subscription->id}})">
                                    {{__('Cancel')}}
                                </flux:button>
                            @endif
                        </div>
                    </flux:card>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10">
                            <flux:text variant="subtle">
                                {{__('No subscriptions.')}}
                            </flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </div>
            <flux:table class="max-sm:hidden">
                <flux:table.columns>
                    <flux:table.column>
                        {{__('Status')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Plan')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Price')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Payment Method')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Next Payment')}}
                    </flux:table.column>
                    <flux:table.column>
                        &nbsp;
                    </flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->subscriptions as $subscription)
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$subscription->status->getBadgeColor()">
                                    {{$subscription->status->getLabel()}}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>
                                    {{$subscription->price->name}}
                                </div>
                                @if($subscription->trial)
                                    <flux:text size="sm" variant="subtle">
                                        {{__('Trial until :date', [ 'date' => $subscription->trial_ended_at->toDateString()])}}
                                    </flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                $ {{Number::format($subscription->price->price,2)}}
                                / {{$subscription->price->frequency->getLabel()}}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{$subscription->payment_method?->getLabel()}}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{$subscription->next_payment_at?->toDateTimeString()}}
                            </flux:table.cell>
                            <flux:table.cell class="text-right">
                                @if($subscription->cancelable)
                                    <flux:button size="xs" wire:click="startCancellation({{$subscription->id}})">
                                        {{__('Cancel')}}
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="10">
                                <flux:text variant="subtle">
                                    {{__('No subscriptions.')}}
                                </flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        <div class="p-4 sm:p-8 space-y-4 sm:space-y-8">
            <flux:heading size="lg">
                {{__('Payments')}}
            </flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>
                        {{__('Status')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Date')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Amount')}}
                    </flux:table.column>
                    <flux:table.column>
                        {{__('Payment Method')}}
                    </flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse($this->payments as $payment)
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$payment->status->getBadgeColor()">
                                    {{$payment->status->getLabel()}}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{$payment->created_at->toDateTimeString()}}
                            </flux:table.cell>
                            <flux:table.cell>
                                $ {{Number::format($payment->amount, 2)}}
                            </flux:table.cell>
                            <flux:table.cell>
                                {{__(PaymentMethod::parse(data_get($payment, 'vendor_data.payment_method_id'))?->getLabel())}}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="10">
                                {{__('No payments.')}}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
