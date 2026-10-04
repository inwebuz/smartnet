@extends('layouts.studio')
@php($label = $resource === 'categories' ? 'категорию' : 'технологию')
@section('title', $item->exists ? 'Редактирование' : 'Добавление')
@section('content')
<a class="text-sm font-semibold text-violet-700" href="{{ route('studio.resources.index', $resource) }}">← К списку</a>
<h1 class="mt-4 text-4xl font-black">{{ $item->exists ? 'Редактировать' : 'Добавить' }} {{ $label }}</h1>
<form method="post" action="{{ $item->exists ? route('studio.resources.update', [$resource, $item->id]) : route('studio.resources.store', $resource) }}" class="mt-8 max-w-2xl space-y-6 rounded-2xl bg-white p-8 shadow-sm">
    @csrf
    @if($item->exists) @method('PUT') @endif
    @if($resource === 'categories')
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach(['ru' => 'Название на русском', 'uz' => 'O‘zbekcha nomi'] as $locale => $caption)
                <label class="block text-sm font-semibold">{{ $caption }}<input required name="title_{{ $locale }}" value="{{ old('title_'.$locale, $item->title[$locale] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
            @endforeach
        </div>
        <label class="block text-sm font-semibold">English name (optional)<input name="title_en" value="{{ old('title_en', $item->title['en'] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
        <label class="block text-sm font-semibold">Родительская категория
            <select name="parent_id" class="mt-2 w-full rounded-xl border border-slate-200 p-3">
                <option value="">Корневая категория</option>
                @foreach($categories as $category)
                    @unless(in_array($category->id, $excludedCategoryIds, true))
                        <option value="{{ $category->id }}" @selected(old('parent_id', $item->parent_id) == $category->id)>{{ $category->pathTitle('ru') }}</option>
                    @endunless
                @endforeach
            </select>
        </label>
        @include('studio.resources.price-fields')
        @foreach(['ru' => 'Русский', 'uz' => 'O‘zbekcha', 'en' => 'English'] as $locale => $caption)
            <fieldset class="space-y-4 rounded-xl border border-slate-200 p-5"><legend class="px-2 font-bold">{{ $caption }} · страница категории</legend>
                <label class="block text-sm font-semibold">Краткое описание<textarea name="summary_{{ $locale }}" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 p-3">{{ old('summary_'.$locale, $item->summary[$locale] ?? '') }}</textarea></label>
                <label class="block text-sm font-semibold">Полное описание<textarea name="description_{{ $locale }}" rows="4" class="mt-2 w-full rounded-xl border border-slate-200 p-3">{{ old('description_'.$locale, $item->{'description_'.$locale}) }}</textarea></label>
                <label class="block text-sm font-semibold">SEO title<input name="seo_title_{{ $locale }}" value="{{ old('seo_title_'.$locale, $item->seo_title[$locale] ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
                <label class="block text-sm font-semibold">SEO description<textarea name="seo_description_{{ $locale }}" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 p-3">{{ old('seo_description_'.$locale, $item->seo_description[$locale] ?? '') }}</textarea></label>
            </fieldset>
        @endforeach
    @else
        <label class="block text-sm font-semibold">Название технологии<input required name="name" value="{{ old('name', $item->name) }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
        <label class="block text-sm font-semibold">Направление
            <select name="kind" class="mt-2 w-full rounded-xl border border-slate-200 p-3">
                @foreach(['frontend' => 'Frontend', 'backend' => 'Backend', 'design' => 'Дизайн', 'devops' => 'DevOps', 'other' => 'Другое'] as $value => $caption)
                    <option value="{{ $value }}" @selected(old('kind', $item->kind ?: 'other') === $value)>{{ $caption }}</option>
                @endforeach
            </select>
        </label>
    @endif
    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block text-sm font-semibold">Slug<input required name="slug" value="{{ old('slug', $item->slug) }}" pattern="[A-Za-z0-9_-]+" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
        <label class="block text-sm font-semibold">Порядок<input type="number" min="0" required name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
    </div>
    <button class="rounded-xl bg-violet-600 px-6 py-3 font-semibold text-white">Сохранить</button>
</form>
@if($item->exists)
    @if($resource === 'categories')
        @can('publish', $item)<form method="post" action="{{ route('studio.resources.publish', [$resource, $item->id]) }}" class="mt-5">@csrf<button class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">{{ $item->is_published ? 'Снять с публикации' : 'Опубликовать категорию' }}</button></form>@endcan
    @endif
    @can('delete', $item)
        <form method="post" action="{{ route('studio.resources.destroy', [$resource, $item->id]) }}" class="mt-5" onsubmit="return confirm('Удалить запись?')">
            @csrf @method('DELETE')
            <button class="text-sm font-semibold text-red-700">Удалить</button>
        </form>
    @endcan
@endif
@endsection
