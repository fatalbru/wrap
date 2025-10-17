@php use App\Enums\ProductType; @endphp
<div x-data="cardPaymentData" x-on:failed="failed($event)">
    <flux:callout variant="danger" x-cloak x-show="!!errorMessage">
        <flux:callout.text>
            <span x-text="errorMessage"></span>
        </flux:callout.text>
    </flux:callout>
    <div class="space-y-4">
        <flux:callout
            icon="lock"
            :text="__('Card payments are processed by MercadoPago.')"
            color="lime"
            class="font-medium"
        />
        <div wire:ignore class="mp-container">
            <div id="cardPaymentBrick_container"></div>
            <flux:button
                type="button"
                x-on:click="attemptCardPayment"
                variant="primary"
                wire:loading.attr="disabled"
                class="w-full"
                x-bind:disabled="loading"
            >
                <flux:icon.lock x-cloak x-show="!loading" class="size-4"/>
                <flux:icon.loader-circle x-cloak x-show="loading" class="animate-spin size-4"/>
                {{__($this->buttonLabel)}}
            </flux:button>
        </div>
        <flux:text class="text-center" variant="subtle">
            {{__('By clicking :button you authorize :app to charge you according to the terms until you cancel.', [
    'app' => config('app.name'),
    'button' => __($this->buttonLabel)
    ])}}
        </flux:text>
    </div>
</div>

@push('scripts')
    @once
        <script src="https://sdk.mercadopago.com/js/v2"></script>
        <script>
            const cardPaymentData = {
                loading: false,
                cardHandler: null,
                errorMessage: null,
                async attemptCardPayment() {
                    if (!this.loading) {
                        this.loading = true;
                        this.errorMessage = null;
                        this.$wire.$set('card', {
                            ...await this.cardHandler.getFormData(),
                            ...await this.cardHandler.getAdditionalData()
                        });
                        this.$wire.$call('pay');
                    }
                },
                failed($event) {
                    this.loading = false
                    // this.errorMessage = $event.detail.errorMessage
                },
                async init() {
                    const mp = new MercadoPago('{{$this->publicKey}}', {
                        locale: 'en'
                    });

                    this.cardHandler = await mp.bricks().create('cardPayment', 'cardPaymentBrick_container', {
                        initialization: {
                            amount: {{$checkout->total}},
                            payer: {
                                email: "{{$checkout->customer?->email}}",
                            },
                        },
                        customization: {
                            visual: @json(array_merge(config('mrr.card_block_customization'), ['hidePaymentButton' => true])),
                            paymentMethods: {
                                minInstallments: {{$checkout->min_installments}},
                                maxInstallments: {{$checkout->max_installments}},
                            },
                        },
                        callbacks: {
                            onReady: () => {
                                this.$dispatch('card-ready')
                            },
                            onError: (error) => {
                                // callback called to all error cases related to the Brick
                            },
                        },
                    })
                }
            }
        </script>
    @endonce
@endpush
