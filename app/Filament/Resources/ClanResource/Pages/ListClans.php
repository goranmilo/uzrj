<?php

namespace App\Filament\Resources\ClanResource\Pages;

use App\Exports\ClanExport;
use App\Filament\Resources\ClanResource;
use App\Imports\ClanImport;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
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
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $import = new ClanImport();
                    Excel::import($import, $data['file']);

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

                    if (!empty($errors)) {
                        Notification::make()
                            ->title('Greške pri importu')
                            ->body(count($errors) . ' grešaka. Proverite log za detalje.')
                            ->warning()
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

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }
}
