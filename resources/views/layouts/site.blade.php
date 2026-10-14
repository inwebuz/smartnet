@php
    $navigation = [
        'ru' => [
            'services' => 'Услуги',
            'cases' => 'Кейсы',
            'contact' => 'Контакты',
            'cta' => 'Обсудить проект',
            'tagline' => 'Идеи превращаются в цифровые продукты.',
            'description' => 'Smartnet — разработка сайтов и дизайн для вашего бизнеса',
        ],
        'uz' => [
            'services' => 'Xizmatlar',
            'cases' => 'Loyihalar',
            'contact' => 'Aloqa',
            'cta' => 'Loyihani muhokama qilish',
            'tagline' => 'G‘oyalar raqamli mahsulotlarga aylanadi.',
            'description' => 'Smartnet — biznesingiz uchun veb-saytlar va dizayn',
        ],
        'en' => [
            'services' => 'Services',
            'cases' => 'Work',
            'contact' => 'Contact',
            'cta' => 'Discuss a project',
            'tagline' => 'Ideas become digital products.',
            'description' => 'Smartnet — web development and digital design for your business',
        ],
    ][$locale];
    $routeName = request()->route()?->getName();
    $localizedUrl = function (string $language) use ($routeName): string {
        return match ($routeName) {
            'site.services' => route('site.services', $language),
            'site.cases' => route('site.cases', $language),
            'site.contact' => route('site.contact', $language),
            'site.category' => route('site.category', [$language, request()->route('slug')]),
            'site.detail' => route('site.detail', [$language, request()->route('type'), request()->route('slug')]),
            default => route('site.home', $language),
        };
    };
    $canonicalUrl = $localizedUrl($locale);
    $translatedItem = $routeName === 'site.category' ? $category ?? null : $item ?? null;
    $availableLocales = in_array($routeName, ['site.detail', 'site.category'], true) && blank($translatedItem?->title['en'] ?? null) ? ['ru', 'uz'] : ['ru', 'uz', 'en'];
    $pageDescriptions = [
        'ru' => [
            'site.home' => 'Разработка сайтов и цифровых продуктов с продуманным интерфейсом и фокусом на результаты бизнеса.',
            'site.services' => 'Услуги Smartnet: разработка сайтов, дизайн и цифровые решения для бизнеса.',
            'site.cases' => 'Кейсы Smartnet: задачи, решения и результаты цифровых проектов.',
            'site.contact' => 'Свяжитесь со Smartnet, чтобы обсудить разработку сайта или цифрового продукта.',
        ],
        'uz' => [
            'site.home' => 'Qulay interfeys va aniq maqsadlariga yoʻnaltirilgan veb-saytlar hamda raqamli mahsulotlar yaratish.',
            'site.services' => 'Smartnet xizmatlari: veb-sayt yaratish, dizayn va biznes uchun raqamli yechimlar.',
            'site.cases' => 'Smartnet loyihalari: vazifalar, yechimlar va natijalar.',
            'site.contact' => 'Veb-sayt yoki raqamli loyiha haqida gaplashish uchun Smartnet bilan bog‘laning.',
        ],
        'en' => [
            'site.home' => 'Website and digital product development tailored for seamless user experience and business outcomes.',
            'site.services' => 'Explore Smartnet services in web development, design, and digital products.',
            'site.cases' => 'Explore Smartnet projects, solutions, and results.',
            'site.contact' => 'Contact Smartnet to discuss your website or digital product.',
        ],
    ];
    $description = in_array($routeName, ['site.detail', 'site.category'], true)
        ? ($translatedItem->seo_description[$locale] ?? null ?:
        $translatedItem->summary[$locale] ?? $navigation['description'])
        : $pageDescriptions[$locale][$routeName] ?? $navigation['description'];
    $organization = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Smartnet',
        'url' => route('site.home', 'uz'),
        'email' => data_get($settings, 'email.text'),
        'telephone' => data_get($settings, 'phone.text'),
    ]);
    $contactPhone = trim((string) data_get($settings, 'phone.text'));
    $telegramUsername = ltrim(trim((string) data_get($settings, 'telegram.text')), '@');
@endphp
<!doctype html>
<html lang="{{ $locale }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Smartnet')</title>
    <meta name="description" content="@yield('meta_description', $description)">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    @foreach ($availableLocales as $language)
        <link rel="alternate" hreflang="{{ $language }}" href="{{ $localizedUrl($language) }}">
    @endforeach
    @if (request()->query('category'))
        <meta name="robots" content="noindex,follow">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Smartnet')">
    <meta property="og:description" content="@yield('meta_description', $description)">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($routeName === 'site.home')
        <script type="application/ld+json">@php echo json_encode($organization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); @endphp</script>
    @endif
    @if (is_file(public_path('hot')) || is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Smartnet" />
    <link rel="manifest" href="/site.webmanifest" />
</head>

<body class="site-interactive bg-[#f7f8fc] text-slate-900 antialiased">
    <div class="min-h-screen overflow-hidden">
        <header class="relative z-10 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            @if ($contactPhone !== '' || $telegramUsername !== '')
                <div class="bg-slate-950 py-2 text-white">
                    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-6 gap-y-2 px-6 text-sm font-semibold sm:justify-end lg:px-10">
                        @if ($contactPhone !== '')
                            <a class="inline-flex items-center gap-2 hover:text-violet-300" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}">{{ $contactPhone }}</a>
                        @endif
                        @if ($telegramUsername !== '')
                            <a class="inline-flex items-center gap-2 hover:text-violet-300" href="https://t.me/{{ rawurlencode($telegramUsername) }}" target="_blank" rel="noopener noreferrer"
                                aria-label="Telegram: {{ '@' . $telegramUsername }}">
                                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M21.9 4.6a1.5 1.5 0 0 0-1.8-.2L2.7 11a1.4 1.4 0 0 0 .1 2.7l4.4 1.4 1.7 5.1a1.4 1.4 0 0 0 2.4.5l2.5-2.6 4.4 3.2a1.5 1.5 0 0 0 2.3-.9L22.4 6a1.5 1.5 0 0 0-.5-1.4ZM9.7 14.7l8.1-6.4-6.4 7.8-.8 2.1-.9-3.5Z" />
                                </svg>
                                <span>{{ '@' . $telegramUsername }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-5 lg:px-10" aria-label="Navigation">
                <a href="{{ route('site.home', $locale) }}" class="text-2xl font-black tracking-tight text-slate-950">smartnet<span class="text-violet-600">.</span></a>
                <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 lg:flex">
                    @foreach (['services', 'cases', 'contact'] as $page)
                        <a class="hover:text-violet-700" href="{{ route('site.' . $page, $locale) }}">{{ $navigation[$page] }}</a>
                    @endforeach
                </div>
                <div class="flex items-center gap-3">
                    @foreach ($availableLocales as $language)
                        @if ($language !== $locale)
                            <a class="text-sm font-semibold text-slate-500 hover:text-slate-900" href="{{ $localizedUrl($language) }}" lang="{{ $language }}">{{ strtoupper($language) }}</a>
                        @endif
                    @endforeach
                    <a href="{{ route('site.contact', $locale) }}"
                        class="hidden whitespace-nowrap rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-700 sm:inline-flex">{{ $navigation['cta'] }}</a>
                    <a href="{{ route('site.contact', $locale) }}" class="inline-flex rounded-full bg-slate-950 p-3 text-white hover:bg-violet-700 sm:hidden"
                        aria-label="{{ $navigation['cta'] }}"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M3 5h18v14H3z" />
                            <path d="m3 6 9 7 9-7" />
                        </svg></a>
                </div>
            </nav>
        </header>
        <main>@yield('content')</main>
        <footer class="mt-24 border-t border-slate-200 bg-white px-6 py-12 text-sm text-slate-500 lg:px-10">
            <div class="mx-auto flex max-w-7xl flex-col justify-between gap-6 md:flex-row">
                <div><strong class="text-xl text-slate-950">smartnet.</strong></div>
                <div class="flex flex-wrap gap-6">
                    @foreach (['services', 'cases', 'contact'] as $page)
                        <a href="{{ route('site.' . $page, $locale) }}">{{ $navigation[$page] }}</a>
                    @endforeach
                </div>
                <span>© {{ date('Y') }} Smartnet</span>
            </div>
        </footer>
    </div>
</body>

</html>
