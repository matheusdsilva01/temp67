<?php

use App\Actions\DeleteFileAction;
use App\Filament\Resources\Files\Pages\ListFiles;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to the administration login', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('non-administrators cannot access the administration panel', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('administrators can access the administration panel', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('administrators can upload a file from the files page', function (): void {
    Storage::fake('local');
    $admin = User::factory()->admin()->create();
    $uploadedFile = UploadedFile::fake()->create('documento.txt', 512, 'text/plain');

    $this->actingAs($admin);

    Livewire::test(ListFiles::class)
        ->callAction('upload', data: ['file' => $uploadedFile])
        ->assertHasNoFormErrors()
        ->assertNotified('Arquivo enviado');

    $file = File::sole();

    expect($file->original_name)->toBe('documento.txt')
        ->and($file->mime_type)->toBe('text/plain')
        ->and($file->size)->toBe(512 * 1024);
    Storage::disk('local')->assertExists($file->path);
});

test('admin upload rejects files larger than ten megabytes', function (): void {
    Storage::fake('local');
    $admin = User::factory()->admin()->create();
    $uploadedFile = UploadedFile::fake()->create('grande.bin', 10 * 1024 + 1);

    $this->actingAs($admin);

    Livewire::test(ListFiles::class)
        ->callAction('upload', data: ['file' => $uploadedFile])
        ->assertHasFormErrors(['file' => 'O arquivo não pode ter mais de 10 MB.']);

    expect(File::query()->doesntExist())->toBeTrue();
    Storage::disk('local')->assertDirectoryEmpty('files');
});

test('an administrator command creates an account with panel access', function (): void {
    $this->artisan('app:create-admin', [
        'name' => 'Administrador',
        'email' => 'admin@example.com',
    ])
        ->expectsQuestion('Password', 'secret-password')
        ->assertSuccessful();

    $admin = User::sole();

    expect($admin->name)->toBe('Administrador')
        ->and($admin->email)->toBe('admin@example.com')
        ->and($admin->is_admin)->toBeTrue()
        ->and($admin->password)->not->toBe('secret-password');
});

test('deleting a file removes its stored content and metadata', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('files/to-delete.txt', 'temporary content');
    $file = new File([
        'disk' => 'local',
        'path' => 'files/to-delete.txt',
        'original_name' => 'to-delete.txt',
        'mime_type' => 'text/plain',
        'size' => 17,
        'expires_at' => now()->addWeek(),
    ]);
    $file->public_id = (string) Str::uuid();
    $file->save();

    app(DeleteFileAction::class)->handle($file);

    $this->assertModelMissing($file);
    Storage::disk('local')->assertMissing('files/to-delete.txt');
});
