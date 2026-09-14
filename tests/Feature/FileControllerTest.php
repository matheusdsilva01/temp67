<?php

use App\Models\File;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('upload form is displayed', function () {
    $this->withoutVite();

    $response = $this->get(route('home'));

    $response->assertSee('Selecione um arquivo');
});

test('anonymous visitor can upload a file that expires after seven days', function () {
    Storage::fake('local');
    $this->travelTo('2026-09-13 12:00:00');
    $file = UploadedFile::fake()->create('anotacoes.txt', 512, 'text/plain');

    $response = $this->post(route('files.store'), ['file' => $file]);

    $storedFile = File::sole();
    $response
        ->assertRedirectToRoute('home')
        ->assertSessionHas('file.name', 'anotacoes.txt');
    expect($storedFile->disk)->toBe('local')
        ->and($storedFile->original_name)->toBe('anotacoes.txt')
        ->and($storedFile->mime_type)->toBe('text/plain')
        ->and($storedFile->size)->toBe(512 * 1024)
        ->and($storedFile->expires_at->toDateTimeString())->toBe('2026-09-20 12:00:00');
    Storage::disk('local')->assertExists($storedFile->path);
});

test('file is required', function () {
    Storage::fake('local');

    $response = $this->from(route('home'))->post(route('files.store'));

    $response
        ->assertRedirectToRoute('home')
        ->assertInvalid(['file' => 'Selecione um arquivo para enviar.']);
    expect(File::count())->toBe(0);
    Storage::disk('local')->assertDirectoryEmpty('files');
});

test('file cannot exceed ten megabytes', function () {
    Storage::fake('local');
    $file = UploadedFile::fake()->create('grande.bin', 10 * 1024 + 1);

    $response = $this->from(route('home'))->post(route('files.store'), ['file' => $file]);

    $response
        ->assertRedirectToRoute('home')
        ->assertInvalid(['file' => 'O arquivo não pode ter mais de 10 MB.']);
    expect(File::count())->toBe(0);
    Storage::disk('local')->assertDirectoryEmpty('files');
});

test('file can have exactly ten megabytes', function () {
    Storage::fake('local');
    $file = UploadedFile::fake()->create('limite.bin', 10 * 1024);

    $response = $this->post(route('files.store'), ['file' => $file]);

    $storedFile = File::sole();
    $response->assertRedirectToRoute('home');
    expect($storedFile->size)->toBe(10 * 1024 * 1024);
    Storage::disk('local')->assertExists($storedFile->path);
});

test('original filename is escaped in the confirmation', function () {
    Storage::fake('local');
    $this->withoutVite();
    $file = UploadedFile::fake()->create('relatorio & resumo.txt', 1, 'text/plain');

    $response = $this->followingRedirects()->post(route('files.store'), ['file' => $file]);

    $response
        ->assertSee('relatorio &amp; resumo.txt', escape: false)
        ->assertDontSee('relatorio & resumo.txt', escape: false);
});

test('upload endpoint is limited to ten attempts per minute for an ip address', function () {
    Storage::fake('local');
    $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.10']);

    foreach (range(1, 10) as $attempt) {
        $this->post(route('files.store'), [
            'file' => UploadedFile::fake()->create("arquivo-{$attempt}.txt", 1),
        ])->assertRedirectToRoute('home');
    }

    $response = $this->post(route('files.store'), [
        'file' => UploadedFile::fake()->create('bloqueado.txt', 1),
    ]);

    $response->assertTooManyRequests();
    expect(File::count())->toBe(10);
});

test('expired files are pruned with their stored files', function () {
    Storage::fake('local');
    $this->travelTo('2026-09-20 12:00:00');
    Storage::disk('local')->put('files/expired.txt', 'expired');
    Storage::disk('local')->put('files/active.txt', 'active');

    $expiredFile = new File([
        'disk' => 'local',
        'path' => 'files/expired.txt',
        'original_name' => 'expired.txt',
        'mime_type' => 'text/plain',
        'size' => 7,
        'expires_at' => '2026-09-20 11:59:59',
    ]);
    $expiredFile->save();
    $activeFile = new File([
        'disk' => 'local',
        'path' => 'files/active.txt',
        'original_name' => 'active.txt',
        'mime_type' => 'text/plain',
        'size' => 6,
        'expires_at' => '2026-09-20 12:00:01',
    ]);
    $activeFile->save();

    $this->artisan('model:prune', ['--model' => [File::class]])->assertSuccessful();

    $this->assertModelMissing($expiredFile);
    $this->assertModelExists($activeFile);
    Storage::disk('local')->assertMissing('files/expired.txt');
    Storage::disk('local')->assertExists('files/active.txt');
});
