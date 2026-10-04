@extends('layouts.site')
@section('title', ($type === 'services' ? ($locale === 'ru' ? 'Услуги' : 'Xizmatlar') : ($locale === 'ru' ? 'Кейсы' : 'Loyihalar')) . ' — Smartnet')
@section('content')
<section class="mx-auto max-w-7xl px-6 py-24 lg:px-10">
    <p class="text-sm font-bold uppercase tracking-[.2em] text-violet-600">Smartnet / {{ $type === 'services' ? '01' : '02' }}</p>
    <h1 class="mt-4 text-5xl font-black">{{ $type === 'services' ? ($locale === 'ru' ? 'Наши услуги' : 'Xizmatlarimiz') : ($locale === 'ru' ? 'Наши проекты' : 'Loyihalarimiz') }}</h1>
    <p class="mt-6 max-w-2xl text-lg text-slate-600">{{ $type === 'services' ? ($locale === 'ru' ? 'От идеи до запуска — помогаем создавать сильные цифровые продукты.' : 'G‘oyadan ishga tushirishgacha — kuchli raqamli mahsulotlar yaratamiz.') : ($locale === 'ru' ? 'Здесь скоро появятся истории наших работ.' : 'Bu yerda tez orada ishlarimiz tarixi paydo bo‘ladi.') }}</p>
    @if($type === 'services' && $categories->isNotEmpty())
        <nav class="mt-10 flex flex-wrap gap-3" aria-label="{{ $locale === 'ru' ? 'Категории услуг' : 'Xizmat toifalari' }}">
            <a href="{{ route('site.services', $locale) }}" class="rounded-full px-5 py-2 text-sm font-semibold {{ $selectedCategory ? 'bg-slate-900 text-white' : 'bg-violet-600 text-white' }}">{{ $locale === 'ru' ? 'Все услуги' : 'Barcha xizmatlar' }}</a>
            @foreach($categories as $category)
                <a href="{{ route('site.services', [$locale, 'category' => $category->slug]) }}" class="rounded-full px-5 py-2 text-sm font-semibold {{ $selectedCategory?->id === $category->id ? 'bg-violet-600 text-white' : 'bg-white text-slate-700 ring-1 ring-slate-200' }}">{{ $category->pathTitle($locale) }}</a>
            @endforeach
        </nav>
    @endif
    <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse(($type === 'services' ? $services : $cases) as $item)
            <a href="{{ route('site.detail', [$locale, $type, $item->slug]) }}" class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">
                <div class="mb-10 h-32 rounded-2xl bg-gradient-to-br from-violet-200 to-cyan-100"></div>
                @if($type === 'services' && $item->category)<p class="mb-3 text-xs font-bold uppercase tracking-wider text-violet-700">{{ $item->category->pathTitle($locale) }}</p>@endif
                <h2 class="text-2xl font-bold">{{ $item->title[$locale] }}</h2>
                <p class="mt-4 text-slate-600">{{ $item->summary[$locale] }}</p>
                @if($item->technologies->isNotEmpty())<div class="mt-5 flex flex-wrap gap-2">@foreach($item->technologies as $technology)<span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $technology->name }}</span>@endforeach</div>@endif
                <span class="mt-8 block font-semibold text-violet-700">{{ $locale === 'ru' ? 'Подробнее' : 'Batafsil' }} →</span>
            </a>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-slate-600 md:col-span-2">{{ $locale === 'ru' ? 'Материалы готовятся. Свяжитесь с нами, чтобы обсудить вашу задачу.' : 'Ma’lumotlar tayyorlanmoqda. Loyihangizni muhokama qilish uchun biz bilan bog‘laning.' }}</div>
        @endforelse
    </div>
</section>
@endsection
