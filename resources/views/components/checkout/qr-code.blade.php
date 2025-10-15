<div class="hidden sm:block" wire:ignore x-data="qrData">
    <flux:modal.trigger name="qr-code">
        <flux:callout icon="qr-code" class="cursor-pointer">
            <flux:callout.heading>
                {{__('Prefer to pay from your phone?')}}
            </flux:callout.heading>
            <flux:callout.text>
                {{__('Click here and scan with your camera.')}}
            </flux:callout.text>
        </flux:callout>
    </flux:modal.trigger>
    <flux:modal name="qr-code" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{__('QR Code')}}
                </flux:heading>
                <flux:text class="mt-2">
                    {{__('Scan with your phone camera and pay from there.')}}
                </flux:text>
            </div>
            <div class="flex flex-col items-center space-y-4">
                <div id="qr-code" class="w-60 mx-auto"></div>
            </div>
        </div>
    </flux:modal>
</div>

@push('scripts')
    @once
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
                integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            const qrData = {
                init() {
                    new QRCode(document.getElementById("qr-code"), "{{$url}}");
                }
            }
        </script>
    @endonce
@endpush
