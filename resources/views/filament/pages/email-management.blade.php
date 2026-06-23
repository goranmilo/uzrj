<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Slanje izveštaja --}}
        <x-filament::section>
            <x-slot name="heading">
                Slanje izveštaja
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-medium text-blue-700 mb-2">Mesečni izveštaj</h4>
                    <p class="text-sm text-blue-600 mb-4">
                        Pošaljite mesečni izveštaj svim aktivnim članovima sa statusom članarine, bodovima i predstojećim edukacijama.
                    </p>
                    <x-filament::button 
                        wire:click="posaljiMesecniIzvestaj"
                        color="primary"
                        class="w-full">
                        <x-heroicon-o-envelope class="w-5 h-5 mr-2" />
                        Pošalji svima
                    </x-filament::button>
                </div>

                <div class="p-4 bg-yellow-50 rounded-lg">
                    <h4 class="font-medium text-yellow-700 mb-2">Podsetnik za članarinu</h4>
                    <p class="text-sm text-yellow-600 mb-4">
                        Pošaljite podsetnik članovima koji imaju neplaćenu članarinu.
                    </p>
                    <x-filament::button 
                        wire:click="posaljiPodsetnikClanarine"
                        color="warning"
                        class="w-full">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 mr-2" />
                        Pošalji podsetnike
                    </x-filament::button>
                </div>

                <div class="p-4 bg-red-50 rounded-lg">
                    <h4 class="font-medium text-red-700 mb-2">Upozorenje za bodove</h4>
                    <p class="text-sm text-red-600 mb-4">
                        Pošaljite upozorenje članovima koji ne ispunjavaju godišnji minimum bodova.
                    </p>
                    <x-filament::button 
                        wire:click="posaljiUpozorenjeBodovi"
                        color="danger"
                        class="w-full">
                        <x-heroicon-o-star class="w-5 h-5 mr-2" />
                        Pošalji upozorenja
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- Zakazano slanje --}}
        <x-filament::section>
            <x-slot name="heading">
                Zakazano slanje
            </x-slot>

            <div class="space-y-4">
                <p class="text-sm text-gray-600">
                    Sistem automatski šalje mejlove prema sledećem rasporedu:
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium">Mesečni izveštaj</p>
                        <p class="text-sm text-gray-500">Svakog 1. u mesecu</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium">Podsetnik za članarinu</p>
                        <p class="text-sm text-gray-500">Sakog ponedeljka</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="font-medium">Upozorenje za bodove</p>
                        <p class="text-sm text-gray-500">Svakog 1. u mesecu</p>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Konfiguracija --}}
        <x-filament::section>
            <x-slot name="heading">
                Konfiguracija
            </x-slot>

            <div class="space-y-4">
                <p class="text-sm text-gray-600">
                    Konfiguracija SMTP servera se nalazi u <code>.env</code> fajlu:
                </p>

                <div class="p-4 bg-gray-100 rounded-lg font-mono text-sm">
                    <p>MAIL_MAILER=smtp</p>
                    <p>MAIL_HOST=smtp.gmail.com</p>
                    <p>MAIL_PORT=587</p>
                    <p>MAIL_USERNAME=vaskontakt@gmail.com</p>
                    <p>MAIL_PASSWORD=***</p>
                    <p>MAIL_ENCRYPTION=tls</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
