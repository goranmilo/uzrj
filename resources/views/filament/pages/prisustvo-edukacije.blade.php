<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info o edukaciji --}}
        <x-filament::section>
            <x-slot name="heading">
                Informacije o edukaciji
            </x-slot>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Naziv</p>
                    <p class="font-medium">{{ $record->naziv }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Datum</p>
                    <p class="font-medium">{{ $record->datum_pocetka->format('d.m.Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Lokacija</p>
                    <p class="font-medium">{{ $record->lokacija }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Bodovi</p>
                    <p class="font-medium">{{ $record->bodovi }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Tabela prisustva --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
