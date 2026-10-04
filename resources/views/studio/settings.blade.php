@extends('layouts.studio')
@section('title', 'Контакты и тексты сайта')
@section('content')
<h1 class="text-4xl font-black">Контакты и тексты сайта</h1>
<p class="mt-3 text-slate-500">Здесь можно изменить контакты и SEO-текст внизу главной страницы.</p>
@can('update', \App\Models\SiteSetting::first() ?? new \App\Models\SiteSetting)
    <form method="post" action="{{ route('studio.settings.update') }}" class="mt-8 max-w-2xl space-y-7 rounded-2xl bg-white p-8 shadow-sm">
        @csrf @method('PUT')
        <fieldset class="space-y-5">
            <legend class="text-xl font-bold">Контакты</legend>
            @foreach(['email' => 'Email', 'phone' => 'Телефон', 'telegram' => 'Telegram', 'location_ru' => 'Адрес / локация (RU)', 'location_uz' => 'Manzil (UZ)', 'location_en' => 'Location (EN)'] as $key => $label)
                <label class="block text-sm font-semibold">{{ $label }}<input name="{{ $key }}" value="{{ old($key, data_get($settings, $key.'.text')) }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
            @endforeach
        </fieldset>
        <fieldset class="space-y-5">
            <legend class="text-xl font-bold">SEO-текст главной страницы</legend>
            @foreach(['ru' => 'Русский', 'uz' => 'O‘zbekcha', 'en' => 'English'] as $language => $label)
                <label class="block text-sm font-semibold">{{ $label }}<textarea name="home_seo_{{ $language }}" rows="6" class="mt-2 w-full rounded-xl border border-slate-200 p-3">{{ old('home_seo_'.$language, data_get($settings, 'home_seo_'.$language.'.text')) }}</textarea></label>
            @endforeach
        </fieldset>
        <button class="rounded-xl bg-violet-600 px-6 py-3 font-semibold text-white">Сохранить</button>
    </form>
@else
    <div class="mt-8 rounded-2xl bg-white p-8">@foreach($settings as $key => $value)<p class="mb-3"><strong>{{ $key }}:</strong> {{ $value['text'] ?? '' }}</p>@endforeach</div>
@endcan
@endsection
