<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Status 2FA --}}
        <x-filament::section>
            <x-slot name="heading">
                Status dvofaktorske autentifikacije
            </x-slot>

            <div class="flex items-center gap-3">
                @if($twoFactorEnabled)
                    <x-heroicon-o-shield-check class="w-8 h-8 text-success-500" />
                    <div>
                        <p class="font-medium text-success-600">2FA je omogućen</p>
                        <p class="text-sm text-gray-500">Vaš nalog je zaštićen dvofaktorskom autentifikacijom.</p>
                    </div>
                @else
                    <x-heroicon-o-shield-exclamation class="w-8 h-8 text-warning-500" />
                    <div>
                        <p class="font-medium text-warning-600">2FA nije omogućen</p>
                        <p class="text-sm text-gray-500">Preporučujemo da omogućite 2FA za dodatnu sigurnost.</p>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- QR Code i setup --}}
        @if($qrCodeSvg)
            <x-filament::section>
                <x-slot name="heading">
                    Podešavanje 2FA
                </x-slot>

                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Skinite QR kod koristeći aplikaciju za autentifikaciju (Google Authenticator, Authy, ili sličnu).
                    </p>

                    <div class="flex justify-center p-4 bg-white rounded-lg">
                        {!! $qrCodeSvg !!}
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Verifikacioni kod
                        </label>
                        <input type="text" 
                               id="2fa-code" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Unesite 6-cifreni kod"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               autocomplete="off">
                    </div>

                    <x-filament::button 
                        wire:click="confirmTwoFactor"
                        class="w-full">
                        Potvrdi i aktiviraj 2FA
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- Recovery kodovi --}}
        @if($recoveryCodes)
            <x-filament::section>
                <x-slot name="heading">
                    Recovery kodovi
                </x-slot>

                <div class="space-y-4">
                    <p class="text-sm text-gray-600">
                        Sačuvajte ove kodove na bezbednom mestu. Možete ih koristiti za pristup nalogu ako izgubite pristup aplikaciji za autentifikaciju.
                    </p>

                    <div class="p-4 bg-gray-50 rounded-lg font-mono text-sm">
                        {!! nl2br(e($recoveryCodes)) !!}
                    </div>

                    <x-filament::button 
                        wire:click="regenerateRecoveryCodes"
                        color="gray"
                        size="sm">
                        Regeneriši recovery kodove
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- Akcije --}}
        <x-filament::section>
            <div class="flex gap-3">
                @if(!$twoFactorEnabled)
                    <x-filament::button 
                        wire:click="enableTwoFactor"
                        color="success">
                        <x-heroicon-o-shield-check class="w-5 h-5 mr-2" />
                        Omogući 2FA
                    </x-filament::button>
                @else
                    <x-filament::button 
                        wire:click="disableTwoFactor"
                        color="danger"
                        outlined>
                        <x-heroicon-o-shield-exclamation class="w-5 h-5 mr-2" />
                        Onemogući 2FA
                    </x-filament::button>
                @endif
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
