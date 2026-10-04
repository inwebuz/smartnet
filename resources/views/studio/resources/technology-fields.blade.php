@if($resource === 'services')
    <label class="block text-sm font-semibold">Категория услуги
        <select name="service_category_id" class="mt-2 w-full rounded-xl border border-slate-200 p-3">
            <option value="">Без категории</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('service_category_id', $item->service_category_id) == $category->id)>{{ $category->pathTitle('ru') }}</option>
            @endforeach
        </select>
    </label>
@endif
<fieldset class="rounded-xl border border-slate-200 p-5">
    <legend class="px-2 font-bold">Стек технологий</legend>
    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        @forelse($technologies as $technology)
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="technology_ids[]" value="{{ $technology->id }}" @checked(in_array($technology->id, old('technology_ids', $item->exists ? $item->technologies->pluck('id')->all() : []))) class="accent-violet-600">{{ $technology->name }}</label>
        @empty
            <p class="text-sm text-slate-500">Сначала добавьте технологии в соответствующем разделе CRM.</p>
        @endforelse
    </div>
</fieldset>
