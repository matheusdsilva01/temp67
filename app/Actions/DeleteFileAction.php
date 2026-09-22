<?php

namespace App\Actions;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteFileAction
{
    public function handle(File $file): void
    {
        if (! Storage::disk($file->disk)->delete($file->path)) {
            throw new RuntimeException("The file [{$file->id}] could not be deleted from storage.");
        }

        $file->delete();
    }
}
