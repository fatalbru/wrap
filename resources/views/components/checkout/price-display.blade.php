@use(App\ProductType;use Illuminate\Support\Number)
<div class="space-y-4">
    <flux:heading size="lg">
        {{$title}}
    </flux:heading>
    <div class="space-y-2">
        @if($productType === ProductType::SUBSCRIPTION)
            @if($trialDays > 0)
                <div class="text-4xl font-medium tracking-tight">
                    {{trans_choice('[1] One day free|{2,*} :count days free',$trialDays)}}
                </div>
                <flux:text>
                    {{ __('Then $ :amount :frequency starting :date', [
    'amount' => number_format($total, 2),
    'frequency' => $frequencyType->getLabel(),
    'date' => now()->addDays($trialDays)->format('F d Y')
    ]) }}
                </flux:text>
            @else
                <div class="text-4xl font-medium tracking-tight">
                    $ {{number_format($total, 2)}} / {{$frequencyType->getLabel()}}
                </div>
            @endif
        @else
            <ul class="border *:py-2.5 *:px-4 border-zinc-200 rounded-lg bg-white divide-y divide-zinc-200">
                @foreach($items as $item)
                    <li class="flex items-end gap-2 text-zinc-500 justify-between">
                        <div>
                            <div>{{data_get($item, 'name')}}</div>
                            <div class="text-sm">{{ data_get($item, 'quantity') }} x $ {{ Number::format(data_get($item, 'unit_price'), 2) }}</div>
                        </div>
                        <span>
                            $ {{Number::format(data_get($item, 'subtotal'),2)}}
                        </span>
                    </li>
                @endforeach
                <li class="flex items-center justify-between font-medium">
                    <span>{{__('Total')}}</span>
                    <span>$ {{number_format($total, 2)}}</span>
                </li>
            </ul>
        @endif
    </div>
</div>
