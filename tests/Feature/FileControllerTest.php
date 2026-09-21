<?php

use App\Models\File;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

uses(LazilyRefreshDatabase::class);

test('upload form is displayed', function (): void {
    $this->withoutVite();

    $response = $this->get(route('home'));

    $response->assertSee('Selecione um arquivo');
});

test('anonymous visitor can upload a file that expires after seven days', function (): void {
    Storage::fake('local');
    $this->travelTo('2026-09-13 12:00:00');
    $file = UploadedFile::fake()->create('anotacoes.txt', 512, 'text/plain');

    $response = $this->post(route('files.store'), ['file' => $file]);

    $storedFile = File::sole();
    $response
        ->assertRedirectToRoute('home')
        ->assertSessionHas('file.name', 'anotacoes.txt')
        ->assertSessionHas('file.url', route('files.show', ['file' => $storedFile->public_id]));
    $fileUrl = $response->getSession()->get('file.url');
    $this->get($fileUrl)->assertOk();
    $this->travelTo('2026-09-20 12:00:01');
    $this->get($fileUrl)->assertNotFound();
    expect(Str::isUuid($storedFile->public_id))->toBeTrue()
        ->and($storedFile->disk)->toBe('local')
        ->and($storedFile->original_name)->toBe('anotacoes.txt')
        ->and($storedFile->mime_type)->toBe('text/plain')
        ->and($storedFile->size)->toBe(512 * 1024)
        ->and($storedFile->expires_at->toDateTimeString())->toBe('2026-09-20 12:00:00');
    Storage::disk('local')->assertExists($storedFile->path);
});

test('public link displays a stored file inline', function (): void {
    Storage::fake('local');
    $this->travelTo('2026-09-13 12:00:00');
    Storage::disk('local')->put('files/document.txt', 'temporary content');
    $file = new File([
        'disk' => 'local',
        'path' => 'files/document.txt',
        'original_name' => 'document.txt',
        'mime_type' => 'text/plain',
        'size' => 17,
        'expires_at' => '2026-09-20 12:00:00',
    ]);
    $file->public_id = (string) Str::uuid();
    $file->save();
    $url = route('files.show', ['file' => $file->public_id]);

    $response = $this->get($url);

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertHeader('Content-Disposition', 'inline; filename=document.txt')
        ->assertHeaderContains('Cache-Control', 'no-store')
        ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'")
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->baseResponse)->toBeInstanceOf(BinaryFileResponse::class)
        ->and($response->baseResponse->getFile()->getContent())->toBe('temporary content');
});

test('internal id cannot display a stored file', function (): void {
    Storage::fake('local');
    $file = new File([
        'disk' => 'local',
        'path' => 'files/private.txt',
        'original_name' => 'private.txt',
        'mime_type' => 'text/plain',
        'size' => 7,
        'expires_at' => now()->addDay(),
    ]);
    $file->public_id = (string) Str::uuid();
    $file->save();

    $this->get(route('files.show', ['file' => $file->id]))->assertNotFound();
});

test('public link cannot display an expired file', function (): void {
    Storage::fake('local');
    $file = new File([
        'disk' => 'local',
        'path' => 'files/expired.txt',
        'original_name' => 'expired.txt',
        'mime_type' => 'text/plain',
        'size' => 7,
        'expires_at' => now()->subSecond(),
    ]);
    $file->public_id = (string) Str::uuid();
    $file->save();
    $url = route('files.show', ['file' => $file->public_id]);

    $this->get($url)->assertNotFound();
});

test('public link returns not found when stored content is missing', function (): void {
    Storage::fake('local');
    $file = new File([
        'disk' => 'local',
        'path' => 'files/missing.txt',
        'original_name' => 'missing.txt',
        'mime_type' => 'text/plain',
        'size' => 7,
        'expires_at' => now()->addDay(),
    ]);
    $file->public_id = (string) Str::uuid();
    $file->save();
    $url = route('files.show', ['file' => $file->public_id]);

    $this->get($url)->assertNotFound();
});

test('malformed public id returns not found', function (): void {
    $this->get('/files/not-a-uuid')->assertNotFound();
});

test('uploaded files receive different public ids', function (): void {
    Storage::fake('local');

    $this->post(route('files.store'), [
        'file' => UploadedFile::fake()->create('first.txt', 1),
    ])->assertRedirectToRoute('home');
    $this->post(route('files.store'), [
        'file' => UploadedFile::fake()->create('second.txt', 1),
    ])->assertRedirectToRoute('home');

    $publicIds = File::query()->pluck('public_id');

    expect($publicIds)->toHaveCount(2)
        ->and($publicIds->unique())->toHaveCount(2);
});

test('file is required', function (): void {
    Storage::fake('local');

    $response = $this->from(route('home'))->post(route('files.store'));

    $response
        ->assertRedirectToRoute('home')
        ->assertInvalid(['file' => 'Selecione um arquivo para enviar.']);
    expect(File::count())->toBe(0);
    Storage::disk('local')->assertDirectoryEmpty('files');
});

test('file cannot exceed ten megabytes', function (): void {
    Storage::fake('local');
    $file = UploadedFile::fake()->create('grande.bin', 10 * 1024 + 1);

    $response = $this->from(route('home'))->post(route('files.store'), ['file' => $file]);

    $response
        ->assertRedirectToRoute('home')
        ->assertInvalid(['file' => 'O arquivo não pode ter mais de 10 MB.']);
    expect(File::count())->toBe(0);
    Storage::disk('local')->assertDirectoryEmpty('files');
});

test('file can have exactly ten megabytes', function (): void {
    Storage::fake('local');
    $file = UploadedFile::fake()->create('limite.bin', 10 * 1024);

    $response = $this->post(route('files.store'), ['file' => $file]);

    $storedFile = File::sole();
    $response->assertRedirectToRoute('home');
    expect($storedFile->size)->toBe(10 * 1024 * 1024);
    Storage::disk('local')->assertExists($storedFile->path);
});

test('original filename is escaped in the confirmation', function (): void {
    Storage::fake('local');
    $this->withoutVite();
    $file = UploadedFile::fake()->create('relatorio & resumo.txt', 1, 'text/plain');

    $response = $this->followingRedirects()->post(route('files.store'), ['file' => $file]);

    $response
        ->assertSee('relatorio &amp; resumo.txt', escape: false)
        ->assertSee('Abrir arquivo')
        ->assertDontSee('relatorio & resumo.txt', escape: false);
});

test('upload endpoint is limited to ten attempts per minute for an ip address', function (): void {
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

test('expired files are pruned with their stored files', function (): void {
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
    $expiredFile->public_id = (string) Str::uuid();
    $expiredFile->save();

    $activeFile = new File([
        'disk' => 'local',
        'path' => 'files/active.txt',
        'original_name' => 'active.txt',
        'mime_type' => 'text/plain',
        'size' => 6,
        'expires_at' => '2026-09-20 12:00:01',
    ]);
    $activeFile->public_id = (string) Str::uuid();
    $activeFile->save();

    $this->artisan('model:prune', ['--model' => [File::class]])->assertSuccessful();

    $this->assertModelMissing($expiredFile);
    $this->assertModelExists($activeFile);
    Storage::disk('local')->assertMissing('files/expired.txt');
    Storage::disk('local')->assertExists('files/active.txt');
});
