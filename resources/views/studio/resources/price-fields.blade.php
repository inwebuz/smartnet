<div class="grid gap-5 sm:grid-cols-2">
    <label class="block text-sm font-semibold">Цена от
        <input type="number" min="0" step="1" name="price_from" value="{{ old('price_from', $item->price_from) }}" class="mt-2 w-full rounded-xl border border-slate-200 p-3" placeholder="Не указана">
    </label>
    <label class="block text-sm font-semibold">Валюта
        <select name="price_currency" class="mt-2 w-full rounded-xl border border-slate-200 p-3">
            @foreach(['UZS' => 'UZS · сум', 'USD' => 'USD · $', 'EUR' => 'EUR · €', 'RUB' => 'RUB · ₽'] as $code => $label)
                <option value="{{ $code }}" @selected(old('price_currency', $item->price_currency ?: 'UZS') === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
</div>
