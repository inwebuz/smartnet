@php
    $formCopy = [
        'ru' => ['title' => 'Обсудить проект', 'name' => 'Ваше имя', 'phone' => 'Телефон', 'message' => 'Расскажите о задаче', 'submit' => 'Отправить заявку', 'success' => 'Спасибо! Заявка отправлена.', 'error' => 'Проверьте поля и укажите email или телефон.'],
        'uz' => ['title' => 'Loyihani muhokama qilish', 'name' => 'Ismingiz', 'phone' => 'Telefon', 'message' => 'Vazifangiz haqida yozing', 'submit' => 'So‘rov yuborish', 'success' => 'Rahmat! So‘rovingiz yuborildi.', 'error' => 'Maydonlarni tekshiring va email yoki telefon raqamingizni kiriting.'],
        'en' => ['title' => 'Discuss your project', 'name' => 'Your name', 'phone' => 'Phone', 'message' => 'Tell us what you need', 'submit' => 'Send request', 'success' => 'Thank you! Your request has been sent.', 'error' => 'Please check the form and provide an email address or phone number.'],
    ][$locale];
@endphp
<section id="contact-form" class="mt-16 rounded-[2rem] bg-white p-8 shadow-sm ring-1 ring-slate-200 md:p-10">
    <h2 class="text-2xl font-bold">{{ $formCopy['title'] }}</h2>
    @if(session('success'))<p class="mt-5 rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ $formCopy['success'] }}</p>@endif
    @if($errors->any())<p class="mt-5 rounded-xl bg-red-50 p-4 text-red-700">{{ $formCopy['error'] }}</p>@endif
    <form method="post" action="{{ route('site.contact.submit', $locale) }}" class="mt-6 space-y-5">@csrf
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="absolute -left-[9999px]" aria-hidden="true">
        @if($leadService)<input type="hidden" name="service_id" value="{{ $leadService->id }}"><input type="hidden" name="return_page" value="service">@endif
        @if($leadCategory)<input type="hidden" name="service_category_id" value="{{ $leadCategory->id }}"><input type="hidden" name="return_page" value="category">@endif
        <label class="block text-sm font-semibold">{{ $formCopy['name'] }}<input required name="name" value="{{ old('name') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label>
        <div class="grid gap-5 sm:grid-cols-2"><label class="block text-sm font-semibold">Email<input type="email" name="email" value="{{ old('email') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label><label class="block text-sm font-semibold">{{ $formCopy['phone'] }}<input name="phone" value="{{ old('phone') }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3"></label></div>
        <label class="block text-sm font-semibold">{{ $formCopy['message'] }}<textarea required minlength="10" name="message" rows="5" class="mt-2 w-full rounded-xl border border-slate-200 p-3">{{ old('message') }}</textarea></label>
        <button class="rounded-xl bg-violet-600 px-6 py-4 font-semibold text-white hover:bg-violet-700">{{ $formCopy['submit'] }}</button>
    </form>
</section>
