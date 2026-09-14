<?php

namespace App\Http\Controllers;

use App\Actions\StoreFileAction;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Http\RedirectResponse;

class FileController extends Controller
{
    public function store(StoreFileRequest $request, StoreFileAction $storeFile): RedirectResponse
    {
        $file = $storeFile->handle($request->uploadedFile());

        return redirect()->route('home')->with('file', [
            'name' => $file->original_name,
            'expires_at' => $file->expires_at->format('d/m/Y H:i'),
        ]);
    }
}
