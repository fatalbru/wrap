@use(App\Enums\PaymentMethod)
@use(App\Enums\PaymentVendor)
@use(App\Enums\ProductType)
<div class="sm:max-w-5xl w-full mx-auto p-6 sm:p-8 flex flex-col"
     x-data="checkoutData"
     @card-ready.window="ready = true; displayMessage()">
    <div class="flex items-center justify-center flex-col h-full space-y-4 min-h-screen" x-show="!ready">
        <flux:icon name="loader-circle" class="animate-spin"/>
        <flux:text>
            {{__('Preparing checkout...')}}
        </flux:text>
    </div>
    <flux:modal name="error-message-checkout" class="min-w-sm">
        <div class="space-y-4">
            <flux:heading size="lg">
                {{__('Payment failed')}}
            </flux:heading>
            <flux:text>
                {{$errorMessage}}
            </flux:text>
        </div>
    </flux:modal>
    <div class="grid grid-cols-1 sm:grid-cols-2 overflow-hidden rounded-2xl shadow-sm grow" x-cloak x-show="ready">
        <div class="space-y-4 sm:space-y-6 p-4 sm:p-8 bg-zinc-100 flex flex-col">
            <x-application-logo class="w-40"/>
            <x-checkout.price-display :checkout="$checkout"/>
            <x-checkout.qr-code :url="url(route('checkout', $checkout))"/>
        </div>
        <div class="p-6 sm:p-8 space-y-6">
            <div class="ternary-select">
                @foreach(PaymentMethod::cases() as $paymentMethod)
                    <div :class="{ 'selected': paymentMethod === '{{$paymentMethod->value}}'}"
                         x-on:click="paymentMethod = '{{$paymentMethod->value}}'">
                        <div class="absolute top-4 right-4">
                            <flux:icon.circle-check-big
                                class="size-4 text-zinc-600"
                                x-show="paymentMethod === '{{$paymentMethod->value}}'"
                            />
                            <flux:icon.circle
                                class="size-4 text-zinc-600"
                                x-show="paymentMethod !== '{{$paymentMethod->value}}'"
                            />
                        </div>
                        <flux:icon :name="$paymentMethod->getIcon()" class="h-5"/>
                        <div>{{__($paymentMethod->getLabel())}}</div>
                    </div>
                @endforeach
            </div>

            <div x-show="paymentMethod === '{{PaymentMethod::MERCADOPAGO->value}}'">
                <livewire:checkout.wallet-payment :checkout="$checkout"/>
            </div>
            <div x-show="paymentMethod === '{{PaymentMethod::CARD->value}}'">
                <livewire:checkout.card-payment :checkout="$checkout"/>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @once
        <script>
            const checkoutData = {
                ready: false,
                paymentMethod: '{{PaymentMethod::CARD->value}}',
                errorMessage: '{{$errorMessage}}',
                displayMessage() {
                    if (!!this.errorMessage) {
                        window.Flux.modal('error-message-checkout').show()
                    }
                }
            }
        </script>
    @endonce
@endpush
