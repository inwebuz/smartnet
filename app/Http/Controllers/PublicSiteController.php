<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\Service;
use App\Models\SiteSetting;

class PublicSiteController extends Controller
{
    public function home(string $locale)
    {
        return view('site.home', $this->data($locale));
    }

    public function services(string $locale)
    {
        return view('site.list', array_merge($this->data($locale), ['type' => 'services']));
    }

    public function cases(string $locale)
    {
        return view('site.list', array_merge($this->data($locale), ['type' => 'cases']));
    }

    public function contact(string $locale)
    {
        return view('site.contact', $this->data($locale));
    }

    public function detail(string $locale, string $type, string $slug)
    {
        abort_unless(in_array($type, ['services', 'cases'], true), 404);
        $model = $type === 'services' ? Service::class : CaseStudy::class;
        $item = $model::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('site.detail', array_merge($this->data($locale), compact('item', 'type')));
    }

    private function data(string $locale): array
    {
        abort_unless(in_array($locale, ['ru', 'uz'], true), 404);
        app()->setLocale($locale);

        return [
            'locale' => $locale,
            'services' => Service::where('is_published', true)->orderBy('sort_order')->get(),
            'cases' => CaseStudy::where('is_published', true)->orderBy('sort_order')->get(),
            'settings' => SiteSetting::all()->pluck('value', 'key'),
        ];
    }
}
