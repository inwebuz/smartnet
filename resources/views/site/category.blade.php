@extends('layouts.site')
@php
    $copy = [
        'ru' => ['back' => 'Все услуги', 'services' => 'Услуги направления', 'subcategories' => 'Направления', 'empty' => 'Скоро здесь появятся услуги. Расскажите о задаче — подберём решение.'],
        'uz' => ['back' => 'Barcha xizmatlar', 'services' => 'Xizmatlar', 'subcategories' => 'Yo‘nalishlar', 'empty' => 'Tez orada xizmatlar qo‘shiladi. Vazifangizni ayting — yechim topamiz.'],
        'en' => ['back' => 'All services', 'services' => 'Services', 'subcategories' => 'Areas', 'empty' => 'More services are coming. Tell us about your project.'],
    ][$locale];
@endphp
@section('title', (($category->seo_title[$locale] ?? null) ?: $category->title[$locale] . ' — Smartnet'))
@section('content')
<article class="mx-auto max-w-6xl px-6 py-24 lg:px-10">
    <a class="text-sm font-semibold text-violet-700" href="{{ route('site.services', $locale) }}">← {{ $copy['back'] }}</a>
    <p class="mt-12 text-sm font-bold uppercase tracking-[.2em] text-violet-600">{{ $category->pathTitle($locale) }}</p>
    <h1 class="mt-5 text-5xl font-black leading-tight">{{ $category->title[$locale] }}</h1>
    @if($category->summary[$locale] ?? null)<p class="mt-7 max-w-3xl text-xl leading-9 text-slate-600">{{ $category->summary[$locale] }}</p>@endif
    <div class="mt-6 text-lg">@include('site.price', ['priced' => $category])</div>
    @if($category->{'description_'.$locale})<div class="prose prose-lg mt-10 max-w-none whitespace-pre-line text-slate-700">{{ $category->{'description_'.$locale} }}</div>@endif
    @if($children->isNotEmpty())<section class="mt-14"><h2 class="text-2xl font-bold">{{ $copy['subcategories'] }}</h2><div class="mt-6 grid gap-4 sm:grid-cols-2">@foreach($children as $child)<a href="{{ route('site.category', [$locale, $child->slug]) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-lg"><h3 class="text-xl font-bold">{{ $child->title[$locale] }}</h3>@if($child->summary[$locale] ?? null)<p class="mt-3 text-slate-600">{{ $child->summary[$locale] }}</p>@endif<div class="mt-4">@include('site.price', ['priced' => $child])</div></a>@endforeach</div></section>@endif
    <section class="mt-14"><h2 class="text-2xl font-bold">{{ $copy['services'] }}</h2><div class="mt-6 grid gap-5 md:grid-cols-2">@forelse($services as $service)<a href="{{ route('site.detail', [$locale, 'services', $service->slug]) }}" class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200 transition hover:shadow-lg"><h3 class="text-xl font-bold">{{ $service->title[$locale] }}</h3><p class="mt-3 text-slate-600">{{ $service->summary[$locale] }}</p><div class="mt-5">@include('site.price', ['priced' => $service])</div></a>@empty<p class="text-slate-600">{{ $copy['empty'] }}</p>@endforelse</div></section>
    @include('site.lead-form', ['leadService' => null, 'leadCategory' => $category])
</article>
@endsection
