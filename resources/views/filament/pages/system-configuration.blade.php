<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" class="w-full">
                    <x-heroicon-o-check class="w-5 h-5 mr-2" />
                    Sačuvaj podešavanja
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
