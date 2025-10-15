<div class="flex items-center justify-center flex-col h-screen space-y-4" wire:poll="checkPayments">
    <flux:icon name="loader-circle" class="animate-spin"/>
    <flux:text>
        {{__('Processing your payment, please hold...')}}
    </flux:text>
</div>
