<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    public function index(Request $request): View
    {
        $members = Member::query()
            ->with('photo')
            ->where('status', Member::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->get();

        $groups = collect(Member::GROUPS)->mapWithKeys(fn ($group) => [
            $group => $members->where('group', $group)->values(),
        ])->filter(fn (Collection $items) => $items->isNotEmpty());

        $seo = [
            'title' => 'Struktur Pengurus — ' . ($request->getHost()),
            'description' => 'Struktur organisasi dan pengurus yayasan.',
            'canonical' => route('public.members'),
            'type' => 'website',
        ];

        return view($this->templateService->baseView('members'), compact('groups', 'seo'));
    }
}
