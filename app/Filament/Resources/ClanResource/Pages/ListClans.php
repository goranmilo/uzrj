<?php

namespace App\Filament\Resources\ClanResource\Pages;

use App\Filament\Resources\ClanResource;
use App\Imports\ClanImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ListClans extends ListRecords
{
    protected static string $resource = ClanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('Import iz Excel-a')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Excel fajl')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        // Fajl sadrži lične podatke — ostaje privremeni (livewire-tmp)
                        // i ne upisuje se na javni disk.
                        ->storeFiles(false)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $file = $data['file'];

                    if (! $file instanceof TemporaryUploadedFile) {
                        Notification::make()
                            ->title('Import članova')
                            ->body('Fajl nije ispravno primljen. Pokušajte ponovo.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $import = new ClanImport;

                    try {
                        Excel::import($import, $file->getRealPath(), null, static::citac($file));
                    } catch (\Throwable $e) {
                        Log::error('Greška pri importu članova', [
                            'fajl' => $file->getClientOriginalName(),
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Import nije uspeo')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();

                        return;
                    }

                    $imported = $import->getImportedCount();
                    $skipped = $import->getSkippedCount();
                    $errors = $import->getErrors();

                    $message = "Import završen: {$imported} članova uveženo.";
                    if ($skipped > 0) {
                        $message .= " {$skipped} preskočeno.";
                    }

                    Notification::make()
                        ->title('Import članova')
                        ->body($message)
                        ->success()
                        ->send();

                    if (! empty($errors)) {
                        Notification::make()
                            ->title('Greške pri importu')
                            ->body(static::opisGresaka($errors))
                            ->warning()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\Action::make('export')
                ->label('Export u Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => route('excel.export'))
                ->openUrlInNewTab(),
            Actions\Action::make('downloadTemplate')
                ->label('Preuzmi šablon')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => route('excel.template'))
                ->openUrlInNewTab(),
        ];
    }

    /**
     * Sažetak grešaka za notifikaciju — prvih nekoliko redova sa razlogom,
     * ostatak se prati kroz log.
     *
     * @param  list<array{row: mixed, jmbg: mixed, error: string}>  $errors
     */
    protected static function opisGresaka(array $errors, int $prikazi = 5): string
    {
        $stavke = collect($errors)
            ->take($prikazi)
            ->map(function (array $greska): string {
                $oznaka = filled($greska['row'] ?? null)
                    ? "Red {$greska['row']}"
                    : "JMBG {$greska['jmbg']}";

                return "{$oznaka}: {$greska['error']}";
            })
            ->implode(' ');

        $preostalo = count($errors) - min($prikazi, count($errors));

        return $preostalo > 0
            ? $stavke." … i još {$preostalo}. Detalji su u logu."
            : $stavke;
    }

    /**
     * Tip čitača se bira po ekstenziji originalnog fajla — privremeni fajl
     * iz livewire-tmp nema uvek upotrebljivu ekstenziju.
     */
    protected static function citac(TemporaryUploadedFile $file): string
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'csv', 'txt' => ExcelFormat::CSV,
            'xls' => ExcelFormat::XLS,
            default => ExcelFormat::XLSX,
        };
    }
}
