<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Aktuelnosti
        </x-slot>

        @if(count($aktuelnosti) > 0)
            <div class="space-y-4">
                @foreach($aktuelnosti as $vest)
                    <div class="p-4 bg-white rounded-lg border">
                        <h4 class="font-medium">{{ $vest['naslov'] }}</h4>
                        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($vest['datum_objave'])->format('d.m.Y') }}</p>
                        <p class="mt-2 text-sm">{{ Str::limit(strip_tags($vest['sadrzaj']), 150) }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">Nema aktuelnosti.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
