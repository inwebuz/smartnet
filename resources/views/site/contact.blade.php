@extends('layouts.site')
@php
    $copy = [
        'ru' => ['title' => 'Контакты', 'headline' => 'Давайте обсудим ваш проект.', 'intro' => 'Расскажите, что хотите создать. Мы свяжемся с вами и предложим следующий шаг.', 'success' => 'Спасибо! Заявка отправлена.', 'error' => 'Проверьте поля формы и укажите телефон.', 'name' => 'Ваше имя', 'phone' => 'Телефон', 'service' => 'Услуга', 'unknown' => 'Пока не знаю', 'message' => 'О проекте', 'submit' => 'Отправить заявку'],
        'uz' => ['title' => 'Aloqa', 'headline' => 'Loyihangizni muhokama qilaylik.', 'intro' => 'Nima yaratmoqchi ekaningizni ayting. Siz bilan bog‘lanib, keyingi qadamni taklif qilamiz.', 'success' => 'Rahmat! So‘rovingiz yuborildi.', 'error' => 'Maydonlarni tekshiring va telefon raqamingizni kiriting.', 'name' => 'Ismingiz', 'phone' => 'Telefon', 'service' => 'Xizmat', 'unknown' => 'Hali bilmayman', 'message' => 'Loyiha haqida', 'submit' => 'So‘rov yuborish'],
        'en' => ['title' => 'Contact', 'headline' => 'Let’s talk about your project.', 'intro' => 'Tell us what you want to build. We’ll get back to you with a clear next step.', 'success' => 'Thank you! Your request has been sent.', 'error' => 'Please check the form and provide a phone number.', 'name' => 'Your name', 'phone' => 'Phone', 'service' => 'Service', 'unknown' => 'Not sure yet', 'message' => 'About your project', 'submit' => 'Send request'],
    ][$locale];
@endphp
@section('title', $copy['title'] . ' — Smartnet')
@section('content')
<section class="mx-auto grid max-w-7xl gap-16 px-6 py-24 lg:grid-cols-2 lg:px-10">
    <div data-reveal><p class="text-sm font-bold uppercase tracking-[.2em] text-violet-600">03 / {{ $copy['title'] }}</p><h1 class="mt-5 text-5xl font-black leading-tight">{{ $copy['headline'] }}</h1><p class="mt-7 text-lg leading-8 text-slate-600">{{ $copy['intro'] }}</p><div class="mt-12 space-y-3 text-slate-600">@if(data_get($settings, 'email.text'))<p><a href="mailto:{{ data_get($settings, 'email.text') }}">{{ data_get($settings, 'email.text') }}</a></p>@endif @if(data_get($settings, 'phone.text'))<p><a href="tel:{{ preg_replace('/[^\d+]/', '', data_get($settings, 'phone.text')) }}">{{ data_get($settings, 'phone.text') }}</a></p>@endif @if(data_get($settings, 'telegram.text'))<p><a href="https://t.me/{{ rawurlencode(ltrim(trim(data_get($settings, 'telegram.text')), '@')) }}" target="_blank" rel="noopener noreferrer">{{ data_get($settings, 'telegram.text') }}</a></p>@endif @if(data_get($settings, 'location_'.$locale.'.text'))<p>{{ data_get($settings, 'location_'.$locale.'.text') }}</p>@endif</div></div>
    <div data-reveal style="--reveal-delay: 100ms" class="rounded-[2rem] bg-white p-8 shadow-xl shadow-slate-200/60 ring-1 ring-slate-100 md:p-10">
        @if(session('success'))<div class="mb-6 rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ $copy['success'] }}</div>@endif
        @if($errors->any())<div class="mb-6 rounded-xl bg-red-50 p-4 text-red-700">{{ $copy['error'] }}</div>@endif
        <form method="post" action="{{ route('site.contact.submit', $locale) }}" class="space-y-5">@csrf
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="absolute -left-[9999px]" aria-hidden="true">
            <label class="block text-sm font-semibold">{{ $copy['name'] }}<input required name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500"></label>
            <label class="block text-sm font-semibold">{{ $copy['phone'] }}<input required type="tel" autocomplete="tel" name="phone" value="{{ old('phone') }}" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500"></label>
            <label class="block text-sm font-semibold">{{ $copy['service'] }}<select name="service_id" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3"><option value="">{{ $copy['unknown'] }}</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->title[$locale] }}</option>@endforeach</select></label>
            <label class="block text-sm font-semibold">{{ $copy['message'] }}<textarea required name="message" rows="5" class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 outline-none focus:border-violet-500">{{ old('message') }}</textarea></label>
            <button class="w-full rounded-xl bg-violet-600 px-6 py-4 font-semibold text-white hover:bg-violet-700">{{ $copy['submit'] }}</button>
        </form>
    </div>
</section>
@endsection
