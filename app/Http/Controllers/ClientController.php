<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        $clients = Client::with('area:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'name_kana'     => $c->name_kana,
                'area_id'       => $c->area_id,
                'area_name'     => $c->area?->name,
                'contact_name'  => $c->contact_name,
                'contact_email' => $c->contact_email,
                'contact_phone' => $c->contact_phone,
                'industry'      => $c->industry,
                'notes'         => $c->notes,
                'is_active'     => $c->is_active,
            ]);

        $areas = Area::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);

        return Inertia::render('Master/Clients', [
            'clients' => $clients,
            'areas'   => $areas,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'name_kana'     => 'nullable|string|max:100',
            'area_id'       => 'nullable|exists:areas,id',
            'contact_name'  => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'industry'      => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:1000',
            'is_active'     => 'boolean',
        ]);

        Client::create($validated);

        return back()->with('success', '取引先を登録しました。');
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'name_kana'     => 'nullable|string|max:100',
            'area_id'       => 'nullable|exists:areas,id',
            'contact_name'  => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'industry'      => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:1000',
            'is_active'     => 'boolean',
        ]);

        $client->update($validated);

        return back()->with('success', '取引先を更新しました。');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->opportunities()->exists()) {
            return back()->with('error', 'この取引先には案件が紐づいているため削除できません。');
        }

        $client->delete();

        return back()->with('success', '取引先を削除しました。');
    }
}
