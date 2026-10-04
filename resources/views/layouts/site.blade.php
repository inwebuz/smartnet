<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Smartnet — {{ $locale === 'ru' ? 'разработка сайтов и дизайн для вашего бизнеса' : 'biznesingiz uchun veb-saytlar va dizayn' }}">
    <title>@yield('title', 'Smartnet')</title>
    @if (is_file(public_path('hot')) || is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-[#f7f8fc] text-slate-900 antialiased">
    <div class="min-h-screen overflow-hidden">
        <header class="relative z-10 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10" aria-label="Navigation">
                <a href="{{ route('site.home', $locale) }}" class="text-2xl font-black tracking-tight text-slate-950">smartnet<span class="text-violet-600">.</span></a>
                <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                    <a class="hover:text-violet-700" href="{{ route('site.services', $locale) }}">{{ $locale === 'ru' ? 'Услуги' : 'Xizmatlar' }}</a>
                    <a class="hover:text-violet-700" href="{{ route('site.cases', $locale) }}">{{ $locale === 'ru' ? 'Кейсы' : 'Loyihalar' }}</a>
                    <a class="hover:text-violet-700" href="{{ route('site.contact', $locale) }}">{{ $locale === 'ru' ? 'Контакты' : 'Aloqa' }}</a>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $otherLocale = $locale === 'ru' ? 'uz' : 'ru';
                        $switchRoute = request()->route()?->getName();
                        $switchUrl = match ($switchRoute) {
                            'site.services' => route('site.services', $otherLocale),
                            'site.cases' => route('site.cases', $otherLocale),
                            'site.contact' => route('site.contact', $otherLocale),
                            'site.detail' => route('site.detail', [$otherLocale, request()->route('type'), request()->route('slug')]),
                            default => route('site.home', $otherLocale),
                        };
                    @endphp
                    <a class="text-sm font-semibold text-slate-500 hover:text-slate-900" href="{{ $switchUrl }}" hreflang="{{ $otherLocale }}">{{ strtoupper($otherLocale) }}</a>
                    <a href="{{ route('site.contact', $locale) }}" class="rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-700">{{ $locale === 'ru' ? 'Обсудить проект' : 'Loyihani muhokama qilish' }}</a>
                </div>
            </nav>
        </header>
        <main>@yield('content')</main>
        <footer class="mt-24 border-t border-slate-200 bg-white px-6 py-12 text-sm text-slate-500 lg:px-10">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-6 md:flex-row">
                <div><strong class="text-xl text-slate-950">smartnet.</strong><p class="mt-2">{{ $locale === 'ru' ? 'Идеи превращаются в цифровые продукты.' : 'G‘oyalar raqamli mahsulotlarga aylanadi.' }}</p></div>
                <div class="flex flex-wrap gap-6"><a href="{{ route('site.services', $locale) }}">{{ $locale === 'ru' ? 'Услуги' : 'Xizmatlar' }}</a><a href="{{ route('site.cases', $locale) }}">{{ $locale === 'ru' ? 'Кейсы' : 'Loyihalar' }}</a><a href="{{ route('site.contact', $locale) }}">{{ $locale === 'ru' ? 'Контакты' : 'Aloqa' }}</a></div>
                <span>© {{ date('Y') }} Smartnet</span>
            </div>
        </footer>
    </div>
</body>
</html>
