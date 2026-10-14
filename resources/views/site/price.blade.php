@if($priced->price_from !== null)
    <span class="font-semibold {{ $priceClass ?? 'text-violet-700' }}">{{ $locale === 'uz' ? '' : ['ru' => 'от ', 'en' => 'from '][$locale] }}{{ number_format($priced->price_from, 0, '.', ' ') }} {{ $priced->price_currency === 'UZS' ? ['ru' => 'сум', 'uz' => 'so‘m', 'en' => 'UZS'][$locale] : $priced->price_currency }}{{ $locale === 'uz' ? 'dan' : '' }}</span>
@endif
