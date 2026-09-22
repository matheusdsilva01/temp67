<?php

namespace App\Filament\Widgets;

use App\Models\File;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FileStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Arquivos armazenados', File::query()->count()),
            Stat::make('Armazenamento utilizado', $this->formatBytes((int) File::query()->sum('size'))),
            Stat::make('Arquivos ativos', File::query()->where('expires_at', '>', now())->count()),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        return number_format($bytes / 1024 / 1024, 2).' MB';
    }
}
