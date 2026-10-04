@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach(['ru', 'uz', 'en'] as $locale)
@foreach(['home', 'services', 'cases', 'contact'] as $page)
<url><loc>{{ route('site.'.$page, $locale) }}</loc>
@foreach(['ru', 'uz', 'en'] as $other)<xhtml:link rel="alternate" hreflang="{{ $other }}" href="{{ route('site.'.$page, $other) }}" />@endforeach
</url>
@endforeach
@endforeach
@foreach(['services' => $services, 'cases' => $cases] as $type => $items)
@foreach($items as $item)
@foreach(['ru', 'uz', 'en'] as $locale)
@if($locale !== 'en' || filled($item->title['en'] ?? null))
<url><loc>{{ route('site.detail', [$locale, $type, $item->slug]) }}</loc><lastmod>{{ $item->updated_at->toDateString() }}</lastmod>
@foreach(['ru', 'uz', 'en'] as $other)@if($other !== 'en' || filled($item->title['en'] ?? null))<xhtml:link rel="alternate" hreflang="{{ $other }}" href="{{ route('site.detail', [$other, $type, $item->slug]) }}" />@endif @endforeach
</url>
@endif
@endforeach
@endforeach
@endforeach
</urlset>
