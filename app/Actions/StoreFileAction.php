<?php

namespace App\Actions;

use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StoreFileAction
{
    public function handle(UploadedFile $uploadedFile): File
    {
        $disk = 'local';
        $path = $uploadedFile->store('files', $disk);

        if ($path === false) {
            throw new RuntimeException('The uploaded file could not be stored.');
        }

        try {
            $file = new File([
                'disk' => $disk,
                'path' => $path,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => (int) $uploadedFile->getSize(),
                'expires_at' => now()->addDays(7),
            ]);
            $file->save();

            return $file;
        } catch (Throwable $throwable) {
            Storage::disk($disk)->delete($path);

            throw $throwable;
        }
    }
}
