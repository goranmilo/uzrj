<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Forma za skeniranje --}}
        <x-filament::section>
            <x-slot name="heading">
                Skeniranje QR koda
            </x-slot>

            <div class="space-y-4">
                {{ $this->form }}

                <x-filament::button 
                    wire:click="scan"
                    class="w-full"
                    size="xl">
                    <x-heroicon-o-qr-code class="w-6 h-6 mr-2" />
                    Čekiraj prisustvo
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Rezultat poslednjeg skeniranja --}}
        @if($lastResult)
            <x-filament::section>
                <x-slot name="heading">
                    Poslednji rezultat
                </x-slot>

                <div class="p-4 rounded-lg {{ $lastResult['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="flex items-center gap-3">
                        @if($lastResult['success'])
                            <x-heroicon-o-check-circle class="w-8 h-8 text-green-500" />
                            <div>
                                <p class="font-medium text-green-700">{{ $lastResult['message'] }}</p>
                                @if(isset($lastResult['clan']))
                                    <p class="text-green-600">Član: {{ $lastResult['clan'] }}</p>
                                @endif
                                @if(isset($lastResult['bodovi']) && $lastResult['bodovi'] > 0)
                                    <p class="text-green-600">Bodovi: {{ $lastResult['bodovi'] }}</p>
                                @endif
                            </div>
                        @else
                            <x-heroicon-o-x-circle class="w-8 h-8 text-red-500" />
                            <div>
                                <p class="font-medium text-red-700">{{ $lastResult['message'] }}</p>
                                @if(isset($lastResult['clan']))
                                    <p class="text-red-600">Član: {{ $lastResult['clan'] }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- Instrukcije --}}
        <x-filament::section>
            <x-slot name="heading">
                Uputstvo
            </x-slot>

            <div class="prose prose-sm max-w-none">
                <ol class="list-decimal list-inside space-y-2">
                    <li>Izaberite edukaciju sa liste</li>
                    <li>Skenirajte QR kod člana ili ga unesite ručno</li>
                    <li>Pritisnite "Čekiraj prisustvo"</li>
                    <li>Sistem će automatski evidentirati prisustvo i dodeliti bodove</li>
                </ol>

                <p class="mt-4 text-sm text-gray-500">
                    <strong>Napomena:</strong> Svaki QR kod može biti iskorišćen samo jednom po edukaciji. 
                    Duplo skeniranje će biti odbijeno.
                </p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
