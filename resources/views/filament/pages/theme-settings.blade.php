<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" class="w-full">
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />
                    Sačuvaj temu
                </x-filament::button>
            </div>
        </form>

        {{-- Pregled tema --}}
        <x-filament::section>
            <x-slot name="heading">
                Pregled dostupnih tema
            </x-slot>

            @php
                $teme = \App\Filament\Pages\ThemeSettings::getTeme();
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($teme as $key => $temaOption)
                    <div class="p-4 rounded-lg border-2 {{ $tema === $key ? 'border-primary-500' : 'border-gray-200' }}"
                         style="cursor: pointer"
                         wire:click="$set('tema', '{{ $key }}')">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-6 h-6 rounded-full" style="background-color: {{ $temaOption['primary'] }}"></div>
                            <div class="w-4 h-4 rounded-full" style="background-color: {{ $temaOption['primary-dark'] }}"></div>
                            <div class="w-4 h-4 rounded-full" style="background-color: {{ $temaOption['accent'] }}"></div>
                        </div>
                        <p class="font-medium text-sm">{{ $temaOption['naziv'] }}</p>
                        <p class="text-xs text-gray-500">{{ $temaOption['opis'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
