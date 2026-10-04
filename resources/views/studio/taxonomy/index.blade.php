@extends('layouts.studio')
@php($label = $resource === 'categories' ? 'Категории услуг' : 'Стек технологий')
@section('title', $label)
@section('content')
<div class="mb-8 flex flex-wrap items-center justify-between gap-4">
    <div><p class="text-sm font-bold uppercase tracking-[.2em] text-violet-600">CRM / Услуги</p><h1 class="mt-3 text-4xl font-black">{{ $label }}</h1></div>
    @can('create', $model)<a href="{{ route('studio.resources.create', $resource) }}" class="rounded-xl bg-violet-600 px-5 py-3 font-semibold text-white">+ Добавить</a>@endcan
</div>
<div class="divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-sm">
    @forelse($items as $item)
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-5">
            <div>
                <a class="text-lg font-semibold hover:text-violet-700" href="{{ route('studio.resources.edit', [$resource, $item->id]) }}">{{ $resource === 'categories' ? $item->pathTitle('ru') : $item->name }}</a>
                <p class="mt-1 text-sm text-slate-500">{{ $resource === 'categories' ? $item->slug : ucfirst($item->kind).' · '.$item->slug }}</p>
                @if($resource === 'categories')<p class="mt-1 text-xs {{ $item->is_published ? 'text-emerald-700' : 'text-amber-700' }}">{{ $item->is_published ? 'Опубликована' : 'Черновик' }} · {{ $item->price_from !== null ? 'от '.number_format($item->price_from, 0, '.', ' ').' '.$item->price_currency : 'цена не указана' }}</p>@endif
            </div>
            @can('update', $item)<a href="{{ route('studio.resources.edit', [$resource, $item->id]) }}" class="text-sm font-semibold text-violet-700">Изменить →</a>@endcan
        </div>
    @empty
        <p class="p-8 text-slate-500">Записей пока нет.</p>
    @endforelse
</div>
@endsection
