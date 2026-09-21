<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Upload temporário</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 font-sans text-stone-100 antialiased selection:bg-lime-300 selection:text-stone-950">
        <main class="relative isolate flex min-h-screen items-center overflow-hidden px-5 py-12 sm:px-8">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_15%_15%,rgba(163,230,53,0.12),transparent_32%),radial-gradient(circle_at_85%_80%,rgba(120,113,108,0.14),transparent_30%)]"></div>
            <div class="absolute inset-0 -z-10 opacity-20 [background-image:linear-gradient(rgba(255,255,255,.06)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.06)_1px,transparent_1px)] [background-size:36px_36px]"></div>

            <section class="mx-auto grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-stone-900/90 shadow-2xl shadow-black/40 backdrop-blur lg:grid-cols-[0.85fr_1.15fr]">
                <div class="flex flex-col justify-between gap-14 border-b border-white/10 bg-lime-300 p-8 text-stone-950 sm:p-10 lg:border-r lg:border-b-0 lg:p-12">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-full border-2 border-stone-950">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 16V4m0 0L7 9m5-5 5 5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="text-sm font-bold tracking-[0.18em] uppercase">Arquivo temporário</span>
                    </div>

                    <div class="grid gap-5">
                        <p class="text-sm font-semibold tracking-wide uppercase">Envio direto. Sem cadastro.</p>
                        <h1 class="max-w-md text-4xl leading-[0.95] font-black tracking-tight sm:text-5xl lg:text-6xl">
                            Solte o arquivo. O tempo cuida do resto.
                        </h1>
                        <p class="max-w-sm text-base leading-7 font-medium text-stone-800">
                            Seu arquivo permanece privado e expira em 7 dias.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-xs font-bold tracking-wider uppercase">
                        <span>Até 10 MB</span>
                        <span>Qualquer formato</span>
                        <span>7 dias</span>
                    </div>
                </div>

                <div class="flex items-center p-8 sm:p-10 lg:p-14">
                    <div class="grid w-full gap-8">
                        <div class="grid gap-2">
                            <p class="text-xs font-semibold tracking-[0.2em] text-lime-300 uppercase">Novo upload</p>
                            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Selecione um arquivo</h2>
                            <p class="text-sm leading-6 text-stone-400">O arquivo será armazenado fora da área pública do servidor.</p>
                        </div>

                        @if (session('file'))
                            <div class="grid gap-1 rounded-2xl border border-lime-300/30 bg-lime-300/10 px-5 py-4" role="status">
                                <p class="text-sm font-semibold text-lime-300">Upload concluído</p>
                                <p class="break-all text-sm text-stone-200">{{ session('file.name') }}</p>
                                <p class="text-xs text-stone-400">Expira em {{ session('file.expires_at') }}</p>
                                <a href="{{ session('file.url') }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-11 items-center justify-center rounded-xl bg-lime-300 px-4 py-2 text-sm font-bold text-stone-950 transition hover:bg-lime-200 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lime-300">
                                    Abrir arquivo
                                </a>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data" class="grid gap-5">
                            @csrf

                            <label for="file" class="group grid min-h-56 cursor-pointer place-items-center rounded-2xl border border-dashed border-stone-600 bg-stone-950/50 p-6 text-center transition hover:border-lime-300 hover:bg-lime-300/5 focus-within:border-lime-300 focus-within:ring-4 focus-within:ring-lime-300/10">
                                <span class="grid place-items-center gap-4">
                                    <span class="grid size-14 place-items-center rounded-full bg-stone-800 text-lime-300 transition group-hover:-translate-y-1 group-hover:bg-lime-300 group-hover:text-stone-950">
                                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M12 16V4m0 0L7 9m5-5 5 5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="grid gap-1">
                                        <span class="font-semibold">Clique para escolher</span>
                                        <span class="text-sm text-stone-500">máximo de 10 MB</span>
                                    </span>
                                </span>
                                <input id="file" name="file" type="file" class="sr-only" aria-describedby="file-error selected-file">
                            </label>

                            <div id="selected-file" class="hidden items-center justify-between gap-4 rounded-xl border border-stone-700 bg-stone-950/50 px-4 py-3" role="status">
                                <div class="min-w-0">
                                    <p id="selected-file-name" class="truncate text-sm font-semibold text-stone-200"></p>
                                    <p id="selected-file-size" class="text-xs text-stone-400"></p>
                                </div>
                                <button id="remove-file" type="button" class="shrink-0 rounded-lg px-3 py-2 text-sm font-semibold text-red-400 transition hover:bg-red-400/10 hover:text-red-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-400">
                                    Remover
                                </button>
                            </div>

                            @error('file')
                                <p id="file-error" class="text-sm font-medium text-red-400" role="alert">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-lime-300 px-6 py-3 text-sm font-bold text-stone-950 transition hover:bg-lime-200 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lime-300 active:translate-y-px">
                                Enviar arquivo
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
