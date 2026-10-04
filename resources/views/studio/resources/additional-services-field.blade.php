<fieldset class="rounded-xl border border-slate-200 p-5">
    <legend class="px-2 font-bold">Дополнительные услуги</legend>
    <p class="mb-4 text-sm text-slate-500">Цена указывается в валюте основной услуги. Пустая цена означает «по запросу».</p>
    @forelse($additionalServices as $option)
        @php($selected = old('additional_services', $item->exists ? $item->additionalServices->map(fn ($linked) => ['id' => $linked->id, 'price' => $linked->pivot->price, 'sort_order' => $linked->pivot->sort_order])->all() : []))
        @php($linked = collect($selected)->firstWhere('id', $option->id))
        <div class="grid gap-3 border-t border-slate-100 py-4 sm:grid-cols-[1fr_10rem_7rem]">
            <label class="flex items-center gap-3 font-semibold"><input type="checkbox" name="additional_services[{{ $option->id }}][id]" value="{{ $option->id }}" @checked($linked !== null) class="accent-violet-600">{{ $option->title['ru'] }}@unless($option->is_active)<span class="text-xs font-normal text-amber-700">(неактивна)</span>@endunless</label>
            <label class="text-sm">Цена<input type="number" min="0" name="additional_services[{{ $option->id }}][price]" value="{{ $linked['price'] ?? '' }}" class="mt-1 w-full rounded-xl border border-slate-200 p-2"></label>
            <label class="text-sm">Порядок<input type="number" min="0" name="additional_services[{{ $option->id }}][sort_order]" value="{{ $linked['sort_order'] ?? $option->sort_order }}" class="mt-1 w-full rounded-xl border border-slate-200 p-2"></label>
        </div>
    @empty
        <p class="text-sm text-slate-500">Сначала добавьте допуслуги в соответствующем разделе CRM.</p>
    @endforelse
</fieldset>
