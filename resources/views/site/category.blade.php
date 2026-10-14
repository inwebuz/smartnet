@extends('layouts.site')
@php
    $copy = [
        'ru' => ['back' => 'Все услуги', 'services' => 'Услуги направления', 'subcategories' => 'Направления', 'empty' => 'Скоро здесь появятся услуги. Расскажите о задаче — подберём решение.'],
        'uz' => ['back' => 'Barcha xizmatlar', 'services' => 'Xizmatlar', 'subcategories' => 'Yo‘nalishlar', 'empty' => 'Tez orada xizmatlar qo‘shiladi. Vazifangizni ayting — yechim topamiz.'],
        'en' => ['back' => 'All services', 'services' => 'Services', 'subcategories' => 'Areas', 'empty' => 'More services are coming. Tell us about your project.'],
    ][$locale];
    $seoCopy = [
        'ru' => [
            'heading' => 'Об услугах направления',
            'text' => 'Подберём состав работ под цели и текущие процессы вашей компании. Обсудим задачи, необходимые функции и интеграции, затем предложим подходящее решение и оценку проекта.',
        ],
        'uz' => [
            'heading' => 'Yo‘nalish xizmatlari haqida',
            'text' =>
                'Ish hajmini kompaniyangiz maqsadlari va jarayonlariga moslaymiz. Vazifalar, kerakli funksiyalar va integratsiyalarni muhokama qilib, mos yechim hamda loyiha bahosini taklif qilamiz.',
        ],
        'en' => [
            'heading' => 'About these services',
            'text' =>
                'We shape the scope around your company’s goals and existing processes. After discussing the tasks, features and integrations, we suggest a suitable solution and project estimate.',
        ],
    ][$locale];
@endphp
@section('title', $category->seo_title[$locale] ?? null ?: $category->title[$locale] . ' — Smartnet')
@section('content')
    <article class="mx-auto max-w-6xl px-6 py-24 lg:px-10">
        <a class="text-sm font-semibold text-violet-700" href="{{ route('site.services', $locale) }}">← {{ $copy['back'] }}</a>
        <p class="mt-12 text-sm font-bold uppercase tracking-[.2em] text-violet-600">{{ $category->pathTitle($locale) }}</p>
        <h1 data-reveal class="mt-5 text-5xl font-black leading-tight">{{ $category->title[$locale] }}</h1>
        @if ($category->summary[$locale] ?? null)
            <p data-reveal style="--reveal-delay: 80ms" class="mt-7 max-w-3xl text-xl leading-9 text-slate-600">{{ $category->summary[$locale] }}</p>
        @endif
        <div class="mt-6 text-lg">@include('site.price', ['priced' => $category])</div>
        @if ($category->image_path)
            <img data-reveal src="{{ $category->image_path }}" alt="{{ $category->title[$locale] }}" class="mt-10 aspect-video w-full rounded-[2rem] bg-violet-100 object-cover">
        @else
            {{-- <div data-reveal class="mt-10 aspect-[21/9] rounded-[2rem] bg-gradient-to-br from-violet-200 via-indigo-100 to-cyan-100"></div> --}}
        @endif
        @if ($children->isNotEmpty())
            <section class="mt-14">
                <h2 data-reveal class="text-2xl font-bold">{{ $copy['subcategories'] }}</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($children as $child)
                        <a data-reveal href="{{ route('site.category', [$locale, $child->slug]) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 hover:shadow-lg">
                            @if ($child->image_path)
                                <img src="{{ $child->image_path }}" alt="{{ $child->title[$locale] }}" loading="lazy" class="mb-5 aspect-video w-full rounded-xl object-cover">
                            @endif
                            <h3 class="text-xl font-bold">
                                {{ $child->title[$locale] }}</h3>
                            @if ($child->summary[$locale] ?? null)
                                <p class="mt-3 text-slate-600">{{ $child->summary[$locale] }}</p>
                            @endif
                            <div class="mt-4">
                                @include('site.price', ['priced' => $child])</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
        <section class="mt-14">
            <h2 data-reveal class="text-2xl font-bold">{{ $copy['services'] }}</h2>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                @forelse($services as $service)
                    <a data-reveal href="{{ route('site.detail', [$locale, 'services', $service->slug]) }}" class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200 hover:shadow-lg">
                        @if ($service->image_path)
                            <img src="{{ $service->image_path }}" alt="{{ $service->title[$locale] }}" loading="lazy" class="mb-5 aspect-video w-full rounded-xl object-cover">
                        @endif
                        <h3 class="text-xl font-bold">
                            {{ $service->title[$locale] }}</h3>
                        <p class="mt-3 text-slate-600">{{ $service->summary[$locale] }}</p>
                        <div class="mt-5">@include('site.price', ['priced' => $service])</div>
                </a>@empty<p class="text-slate-600">{{ $copy['empty'] }}</p>
                @endforelse
            </div>
        </section>
        <section data-reveal class="mt-20 border-t border-slate-200 pt-12">
            <h2 class="text-3xl font-bold">{{ $category->title[$locale] }} — {{ $seoCopy['heading'] }}</h2>
            <p class="mt-6 max-w-4xl whitespace-pre-line text-lg leading-8 text-slate-600">{{ $category->description[$locale] ?? null ?: $seoCopy['text'] }}</p>
        </section>
        @include('site.lead-form', ['leadService' => null, 'leadCategory' => $category])
    </article>
@endsection
