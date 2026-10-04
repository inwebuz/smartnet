<div class="space-y-4 rounded-xl border border-slate-200 p-5">
    <label class="block text-sm font-semibold">Изображение
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-50 file:px-4 file:py-2 file:font-semibold file:text-violet-700">
    </label>
    <p class="text-xs text-slate-500">JPG, PNG или WebP, до 5 МБ.</p>
    @error('image')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
    @if($item->image_path)
        <img src="{{ $item->image_path }}" alt="Текущее изображение" class="aspect-video w-full max-w-sm rounded-xl bg-slate-100 object-cover">
        <label class="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="remove_image" value="1" class="accent-violet-600"> Удалить изображение</label>
    @endif
</div>
