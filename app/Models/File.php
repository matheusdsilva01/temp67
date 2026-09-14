<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/** @property Carbon $expires_at */
#[Fillable(['disk', 'path', 'original_name', 'mime_type', 'size', 'expires_at'])]
class File extends Model
{
    use HasUuids, Prunable;

    /**
     * Get the query that selects expired files for pruning.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return $this->newQuery()->where('expires_at', '<=', now());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    /**
     * Delete the stored file before removing its metadata.
     */
    protected function pruning(): void
    {
        if (! Storage::disk($this->disk)->delete($this->path)) {
            throw new RuntimeException("The expired file [{$this->id}] could not be deleted.");
        }
    }
}
