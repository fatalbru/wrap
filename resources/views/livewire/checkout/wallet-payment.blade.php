<form wire:submit.prevent="pay" class="space-y-6" x-on:submit="errorMessage = null" x-data="walletPaymentData"
      x-on:failed="failed($event)">
    <flux:callout variant="danger" x-cloak x-show="!!errorMessage">
        <flux:callout.text>
            <span x-text="errorMessage"></span>
        </flux:callout.text>
    </flux:callout>
    <flux:callout variant="secondary">
        <flux:callout.heading>
            {{__('Important notice')}}
        </flux:callout.heading>
        <flux:callout.text>
            {{__('Your e-mail must match your MercadoPago account otherwise payment will be rejected.')}}
        </flux:callout.text>
    </flux:callout>
    <flux:input :label="__('E-mail address')" required type="email" wire:model="email"/>
    <flux:button class="w-full" variant="primary" icon-trailing="arrow-right" type="submit"
                 wire:loading.attr="disabled">
        {{__('Continue to MercadoPago')}}
    </flux:button>
    <flux:text class="text-center" variant="subtle">
        {{__('You will be redirected to Mercado Pago where you can choose from your available payment methods.')}}
    </flux:text>
</form>


@push('scripts')
    @once
        <script>
            const walletPaymentData = {
                errorMessage: null,
                failed($event) {
                    this.errorMessage = $event.detail.errorMessage
                },
            }
        </script>
    @endonce
@endpush
