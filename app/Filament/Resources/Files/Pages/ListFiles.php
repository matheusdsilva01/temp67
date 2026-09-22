<?php

namespace App\Filament\Resources\Files\Pages;

use App\Actions\StoreFileAction;
use App\Filament\Resources\Files\FileResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class ListFiles extends ListRecords
{
    protected static string $resource = FileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Enviar arquivo')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    FileUpload::make('file')
                        ->label('Arquivo')
                        ->required()
                        ->maxSize(10240)
                        ->storeFiles(false)
                        ->validationMessages([
                            'required' => 'Selecione um arquivo para enviar.',
                            'max' => 'O arquivo não pode ter mais de 10 MB.',
                        ]),
                ])
                ->modalHeading('Enviar arquivo')
                ->modalSubmitActionLabel('Enviar')
                ->action(function (array $data): void {
                    $uploadedFile = $data['file'] ?? null;

                    if (! $uploadedFile instanceof UploadedFile) {
                        throw new RuntimeException('The validated upload is unavailable.');
                    }

                    app(StoreFileAction::class)->handle($uploadedFile);

                    Notification::make()
                        ->success()
                        ->title('Arquivo enviado')
                        ->send();
                }),
        ];
    }
}
