<?php

use App\Actions\StoreFileAction;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

afterEach(function () {
    File::flushEventListeners();
});

test('stored file is removed when persistence fails', function () {
    Storage::fake('local');
    File::creating(function (): never {
        throw new RuntimeException('Persistence failed.');
    });
    $uploadedFile = UploadedFile::fake()->create('document.txt', 1, 'text/plain');

    expect(fn () => app(StoreFileAction::class)->handle($uploadedFile))
        ->toThrow(RuntimeException::class, 'Persistence failed.');
    Storage::disk('local')->assertDirectoryEmpty('files');
});
