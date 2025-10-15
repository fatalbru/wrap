@php use App\PaymentVendor;use App\ProductType; @endphp
<div class="p-4 sm:p-8" x-data="completedCheckout" id="checkout">
    <div class="flex items-center justify-center flex-col h-full space-y-12">
        <x-application-logo class="w-40"/>
        <div class="space-y-4 items-center justify-center flex flex-col">
            <flux:icon name="check-circle" class="size-12 text-green-700"/>
            <flux:heading size="xl">
                {{__('Payment completed!')}}
            </flux:heading>
            @if(filled($checkout->redirect_url) && $shouldRedirect)
                <flux:text>
                    {{__('You will be redirected in a few seconds...')}}
                </flux:text>
            @else
                <flux:text>
                    {{__('You may now close this window')}}
                </flux:text>
            @endif
        </div>
    </div>
</div>
@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js"></script>

        <script>
            const completedCheckout = {
                init() {
                    (new JSConfetti()).addConfetti({
                        confettiRadius: 5,
                        confettiNumber: 350,
                    })
                }
            }
        </script>
    @endonce
@endpush
