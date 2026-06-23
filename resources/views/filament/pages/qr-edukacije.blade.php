<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info o edukaciji --}}
        <x-filament::section>
            <x-slot name="heading">
                QR kodovi za edukaciju: {{ $record->naziv }}
            </x-slot>
            
            <p class="text-sm text-gray-500">
                Datum: {{ $record->datum_pocetka->format('d.m.Y H:i') }} | 
                Lokacija: {{ $record->lokacija }} |
                Bodovi: {{ $record->bodovi }}
            </p>
        </x-filament::section>

        {{-- Izbor člana --}}
        <x-filament::section>
            <x-slot name="heading">
                Prikaz QR koda
            </x-slot>

            <div class="space-y-4">
                {{ $this->form }}

                @if($this->getQrSvg())
                    <div class="flex flex-col items-center space-y-4 p-6 bg-white rounded-lg border">
                        <div class="text-center">
                            <p class="text-lg font-medium">{{ $this->getSelectedQrData()['clan'] }}</p>
                            <p class="text-sm text-gray-500">Članski broj: {{ $this->getSelectedQrData()['clanski_broj'] }}</p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 rounded-lg">
                            {!! $this->getQrSvg() !!}
                        </div>

                        <div class="text-sm text-gray-500 text-center">
                            <p>Skenirajte QR kod za evidentiranje prisustva</p>
                        </div>

                        @if($this->getSelectedQrData()['prisutan'])
                            <div class="px-4 py-2 bg-green-100 text-green-700 rounded-lg">
                                ✓ Član je već čekiran
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Pregled svih QR kodova --}}
        <x-filament::section>
            <x-slot name="heading">
                Svi QR kodovi ({{ count($qrCodes) }})
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($qrCodes as $qr)
                    <div class="p-3 bg-white rounded-lg border text-center {{ $qr['prisutan'] ? 'border-green-300' : '' }}">
                        <div class="text-xs font-medium mb-1 truncate">{{ $qr['clan'] }}</div>
                        <div class="p-2 bg-gray-50 rounded">
                            {!! $qr['qr_svg'] !!}
                        </div>
                        @if($qr['prisutan'])
                            <div class="mt-1 text-xs text-green-600">✓</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
