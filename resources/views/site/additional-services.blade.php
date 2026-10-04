@php
    $options = $leadService->additionalServices->filter(fn ($option) => $option->is_active && filled($option->title[$locale] ?? null));
    $quoteCopy = [
        'ru' => ['title' => 'Дополнительные опции', 'hint' => 'Выберите нужные опции и получите предварительную стоимость.', 'base' => 'Базовая стоимость', 'total' => 'Предварительно от', 'request' => 'Стоимость по запросу', 'pending' => 'Расчёт после отправки заявки', 'currency' => 'сум'],
        'uz' => ['title' => 'Qo‘shimcha xizmatlar', 'hint' => 'Kerakli xizmatlarni tanlang va taxminiy narxni ko‘ring.', 'base' => 'Asosiy narx', 'total' => 'Taxminan', 'request' => 'Narx so‘rov asosida', 'pending' => 'Hisob so‘rov yuborilgach tayyorlanadi', 'currency' => 'so‘m'],
        'en' => ['title' => 'Additional options', 'hint' => 'Choose options to see an estimated starting price.', 'base' => 'Base price', 'total' => 'Estimated from', 'request' => 'Price on request', 'pending' => 'Estimate calculated after submission', 'currency' => 'UZS'],
    ][$locale];
    $currencyLabel = $leadService->price_currency === 'UZS' ? $quoteCopy['currency'] : $leadService->price_currency;
@endphp
<fieldset data-service-calculator data-base-price="{{ $leadService->price_from }}" data-currency="{{ $currencyLabel }}" data-total-label="{{ $quoteCopy['total'] }}" data-request-label="{{ $quoteCopy['request'] }}" class="rounded-2xl border border-violet-100 bg-violet-50/50 p-5 sm:p-7">
    <legend class="px-2 text-xl font-bold">{{ $quoteCopy['title'] }}</legend>
    <p class="text-sm text-slate-600">{{ $quoteCopy['hint'] }}</p>
    <div class="mt-5 space-y-3">
        @foreach($options as $option)
            <label class="flex cursor-pointer items-start gap-4 rounded-xl bg-white p-4 ring-1 ring-slate-200 transition-colors hover:ring-violet-400">
                <input type="checkbox" name="additional_service_ids[]" value="{{ $option->id }}" data-option-price="{{ $option->pivot->price }}" @checked(in_array($option->id, old('additional_service_ids', []))) class="mt-1 accent-violet-600">
                <span class="min-w-0 flex-1"><span class="block font-semibold">{{ $option->title[$locale] }}</span>@if($option->description[$locale] ?? null)<span class="mt-1 block text-sm text-slate-600">{{ $option->description[$locale] }}</span>@endif</span>
                <span class="shrink-0 text-sm font-semibold text-violet-700">{{ $option->pivot->price === null ? $quoteCopy['request'] : number_format($option->pivot->price, 0, '.', ' ').' '.$currencyLabel }}</span>
            </label>
        @endforeach
    </div>
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-violet-100 pt-5"><span class="text-sm text-slate-600">{{ $quoteCopy['base'] }}: {{ $leadService->price_from === null ? $quoteCopy['request'] : number_format($leadService->price_from, 0, '.', ' ').' '.$currencyLabel }}</span><strong data-calculator-total aria-live="polite" class="text-lg text-violet-800">{{ $quoteCopy['pending'] }}</strong></div>
</fieldset>
