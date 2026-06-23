<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Statistike --}}
        <x-filament::section>
            <x-slot name="heading">
                Statistike bodovnog sistema
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-600">Ukupno članova</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $statistike['ukupno_clanova'] }}</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-green-600">Ispunjavaju minimum</p>
                    <p class="text-2xl font-bold text-green-700">{{ $statistike['ispunjavaju_minimum'] }}</p>
                    <p class="text-xs text-green-500">{{ $statistike['procenat_minimum'] }}%</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-sm text-red-600">Ne ispunjavaju</p>
                    <p class="text-2xl font-bold text-red-700">{{ $statistike['ne_ispunjavaju_minimum'] }}</p>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg">
                    <p class="text-sm text-purple-600">Ispunjavaju ukupni prag</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $statistike['ispunjavaju_ukupni'] }}</p>
                    <p class="text-xs text-purple-500">{{ $statistike['procenat_ukupni'] }}%</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Pretraga člana --}}
        <x-filament::section>
            <x-slot name="heading">
                Pregled napretka člana
            </x-slot>

            <div class="space-y-4">
                {{ $this->form }}

                @if($clanStatus)
                    <div class="p-6 bg-white rounded-lg border space-y-6">
                        <div class="text-center">
                            <h3 class="text-xl font-bold">{{ $clanStatus['clan'] }}</h3>
                            <p class="text-gray-500">JMBG: {{ $clanStatus['jmbg'] }}</p>
                        </div>

                        {{-- Godišnji minimum --}}
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">Godišnji minimum</span>
                                <span class="text-sm {{ $clanStatus['ispunjava_godisnji'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $clanStatus['bodovi_godina'] }} / {{ $clanStatus['godisnji_minimum'] }} bodova
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full {{ $clanStatus['ispunjava_godisnji'] ? 'bg-green-500' : 'bg-red-500' }}" 
                                     style="width: {{ $clanStatus['procenat_godina'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>{{ $clanStatus['procenat_godina'] }}%</span>
                                @if(!$clanStatus['ispunjava_godisnji'])
                                    <span>Preostaje: {{ $clanStatus['preostalo_godina'] }} bodova</span>
                                @else
                                    <span class="text-green-600">✓ Ispunjeno</span>
                                @endif
                            </div>
                        </div>

                        {{-- Ukupan prag --}}
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">Ukupan prag za period</span>
                                <span class="text-sm {{ $clanStatus['ispunjava_ukupni'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $clanStatus['bodovi_period'] }} / {{ $clanStatus['ukupan_prag'] }} bodova
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full {{ $clanStatus['ispunjava_ukupni'] ? 'bg-green-500' : 'bg-blue-500' }}" 
                                     style="width: {{ $clanStatus['procenat_period'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>{{ $clanStatus['procenat_period'] }}%</span>
                                @if(!$clanStatus['ispunjava_ukupni'])
                                    <span>Preostaje: {{ $clanStatus['preostalo_period'] }} bodova</span>
                                @else
                                    <span class="text-green-600">✓ Ispunjeno</span>
                                @endif
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="flex justify-center">
                            @if($clanStatus['ispunjava_godisnji'] && $clanStatus['ispunjava_ukupni'])
                                <div class="px-4 py-2 bg-green-100 text-green-700 rounded-lg">
                                    ✓ Član ispunjava sve uslove
                                </div>
                            @elseif(!$clanStatus['ispunjava_godisnji'])
                                <div class="px-4 py-2 bg-red-100 text-red-700 rounded-lg">
                                    ⚠ Član NE ispunjava godišnji minimum
                                </div>
                            @else
                                <div class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg">
                                    ⚠ Član ne ispunjava ukupan prag za period
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Članovi ispod minimuma --}}
        @php
            $ispodMinimuma = \App\Services\BodoviService::clanoviIspodMinimuma();
            $isticeLicenca = \App\Services\BodoviService::clanoviIsticeLicenca();
        @endphp

        @if($ispodMinimuma->count() > 0 || $isticeLicenca->count() > 0)
            <x-filament::section>
                <x-slot name="heading">
                    Upozorenja
                </x-slot>

                <div class="space-y-4">
                    @if($ispodMinimuma->count() > 0)
                        <div class="p-4 bg-red-50 rounded-lg">
                            <h4 class="font-medium text-red-700 mb-2">Članovi ispod godišnjeg minimuma ({{ $ispodMinimuma->count() }})</h4>
                            <ul class="list-disc list-inside text-sm text-red-600">
                                @foreach($ispodMinimuma->take(10) as $clan)
                                    <li>{{ $clan->prezime }} {{ $clan->ime }}</li>
                                @endforeach
                                @if($ispodMinimuma->count() > 10)
                                    <li>... i još {{ $ispodMinimuma->count() - 10 }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    @if($isticeLicenca->count() > 0)
                        <div class="p-4 bg-yellow-50 rounded-lg">
                            <h4 class="font-medium text-yellow-700 mb-2">Licenca ističe za manje od 60 dana ({{ $isticeLicenca->count() }})</h4>
                            <ul class="list-disc list-inside text-sm text-yellow-600">
                                @foreach($isticeLicenca->take(10) as $licenca)
                                    <li>{{ $licenca->clan->prezime }} {{ $licenca->clan->ime }} - ističe: {{ $licenca->datum_isteka->format('d.m.Y') }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
