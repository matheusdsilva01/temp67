<?php

namespace App\Http\Controllers;

use App\Actions\StoreFileAction;
use App\Http\Requests\StoreFileRequest;
use App\Models\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileController extends Controller
{
    public function show(File $file): BinaryFileResponse
    {
        abort_if($file->expires_at->lessThanOrEqualTo(now()), 404);

        $disk = Storage::disk($file->disk);

        abort_unless($disk->exists($file->path), 404);

        return response()->file($disk->path($file->path), [
            'Cache-Control' => 'no-store',
            'Content-Security-Policy' => "sandbox; default-src 'none'",
            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ])->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $file->original_name);
    }

    public function store(StoreFileRequest $request, StoreFileAction $storeFile): RedirectResponse
    {
        $file = $storeFile->handle($request->uploadedFile());

        return redirect()->route('home')->with('file', [
            'name' => $file->original_name,
            'expires_at' => $file->expires_at->format('d/m/Y H:i'),
            'url' => route('files.show', ['file' => $file->public_id]),
        ]);
    }
}
