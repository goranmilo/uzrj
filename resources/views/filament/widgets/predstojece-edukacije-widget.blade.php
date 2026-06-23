<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Predstojeće edukacije
        </x-slot>

        @if(count($edukacije) > 0)
            <div class="space-y-4">
                @foreach($edukacije as $edukacija)
                    <div class="p-4 bg-white rounded-lg border">
                        <h4 class="font-medium">{{ $edukacija['naziv'] }}</h4>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Datum:</span>
                                {{ \Carbon\Carbon::parse($edukacija['datum_pocetka'])->format('d.m.Y H:i') }}
                            </div>
                            <div>
                                <span class="text-gray-500">Lokacija:</span>
                                {{ $edukacija['lokacija'] }}
                            </div>
                            <div>
                                <span class="text-gray-500">Bodovi:</span>
                                {{ $edukacija['bodovi'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Nema predstojećih edukacija.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
