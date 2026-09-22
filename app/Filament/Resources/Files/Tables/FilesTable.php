<?php

namespace App\Filament\Resources\Files\Tables;

use App\Actions\DeleteFileAction;
use App\Models\File;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('original_name')
                    ->label('Arquivo')
                    ->searchable(),
                TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->toggleable(),
                TextColumn::make('size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024 / 1024, 2).' MB')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn (File $record): string => $record->expires_at->isPast() ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Ativos',
                        'expired' => 'Expirados',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where('expires_at', '>', now()),
                            'expired' => $query->where('expires_at', '<=', now()),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->using(function (File $record): bool {
                        app(DeleteFileAction::class)->handle($record);

                        return true;
                    }),
            ]);
    }
}
