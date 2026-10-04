@extends('layouts.site')
@php
    $copy = [
        'ru' => ['back' => 'Назад', 'services' => 'Услуга', 'cases' => 'Проект', 'stack' => 'Стек технологий', 'cta' => 'Обсудить похожий проект'],
        'uz' => ['back' => 'Orqaga', 'services' => 'Xizmat', 'cases' => 'Loyiha', 'stack' => 'Texnologiyalar', 'cta' => 'Shunga o‘xshash loyihani muhokama qilish'],
        'en' => ['back' => 'Back', 'services' => 'Service', 'cases' => 'Project', 'stack' => 'Technology stack', 'cta' => 'Discuss a similar project'],
    ][$locale];
    $seoCopy = [
        'ru' => ['heading' => 'Что входит в работу', 'text' => 'Начинаем с обсуждения задачи и ожидаемого результата, затем определяем структуру, функции и этапы разработки. Итоговый состав работ и стоимость зависят от требований проекта.'],
        'uz' => ['heading' => 'Ish jarayoni haqida', 'text' => 'Avval vazifa va kutilgan natijani muhokama qilamiz, keyin tuzilma, funksiyalar va ishlab chiqish bosqichlarini belgilaymiz. Yakuniy ish hajmi va narxi loyiha talablariga bog‘liq.'],
        'en' => ['heading' => 'What the work involves', 'text' => 'We start by discussing the task and expected outcome, then define the structure, features and development stages. The final scope and cost depend on your project requirements.'],
    ][$locale];
@endphp
@section('title', (($item->seo_title[$locale] ?? null) ?: $item->title[$locale] . ' — Smartnet'))
@section('content')
<article class="mx-auto max-w-5xl px-6 py-24 lg:px-10">
    <a class="text-sm font-semibold text-violet-700" href="{{ $type === 'services' ? route('site.services', $locale) : route('site.cases', $locale) }}">← {{ $copy['back'] }}</a>
    <p class="mt-16 text-sm font-bold uppercase tracking-[.2em] text-violet-600">{{ $copy[$type] }}</p>
    @if($type === 'services' && $item->category)<p class="mt-4 text-sm text-slate-500">@if($item->category->is_published)<a class="hover:text-violet-700" href="{{ route('site.category', [$locale, $item->category->slug]) }}">{{ $item->category->pathTitle($locale) }}</a>@else{{ $item->category->pathTitle($locale) }}@endif</p>@endif
    <h1 data-reveal class="mt-5 text-5xl font-black leading-tight">{{ $item->title[$locale] }}</h1><p data-reveal style="--reveal-delay: 80ms" class="mt-8 text-xl leading-9 text-slate-600">{{ $item->summary[$locale] }}</p>
    @if($type === 'services')<p class="mt-6 text-lg">@include('site.price', ['priced' => $item])</p>@endif
    @if($item->image_path)<img data-reveal src="{{ $item->image_path }}" alt="{{ $item->title[$locale] }}" class="mt-14 aspect-video w-full rounded-[2rem] object-cover">@else<div data-reveal class="mt-14 rounded-[2rem] bg-gradient-to-br from-violet-200 via-indigo-100 to-cyan-100 p-16"></div>@endif
    @if($type === 'cases' && ($item->description[$locale] ?? null))<div class="prose prose-lg mt-12 max-w-none whitespace-pre-line text-slate-700">{{ $item->description[$locale] }}</div>@endif
    @if($item->technologies->isNotEmpty())<div class="mt-12"><h2 class="text-lg font-bold">{{ $copy['stack'] }}</h2><div class="mt-4 flex flex-wrap gap-2">@foreach($item->technologies as $technology)<span class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200">{{ $technology->name }}</span>@endforeach</div></div>@endif
    @if($type === 'services')
        <section data-reveal class="mt-20 border-t border-slate-200 pt-12">
            <h2 class="text-3xl font-bold">{{ $item->title[$locale] }} — {{ $seoCopy['heading'] }}</h2>
            <p class="mt-6 max-w-4xl whitespace-pre-line text-lg leading-8 text-slate-600">{{ ($item->description[$locale] ?? null) ?: $seoCopy['text'] }}</p>
        </section>
        @include('site.lead-form', ['leadService' => $item, 'leadCategory' => null])
    @else
        <div class="mt-16 border-t border-slate-200 pt-10"><a href="{{ route('site.contact', $locale) }}" class="rounded-full bg-violet-600 px-7 py-4 font-semibold text-white">{{ $copy['cta'] }} →</a></div>
    @endif
</article>
@endsection
