<?php

namespace App\Http\Controllers\NetworkAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NetworkSettingsController extends Controller
{
    /**
     * Exibe formulário de edição de configurações
     */
    public function edit()
    {
        $network = app('currentNetwork');
        $settings = $network->settings ?? [];

        return view('network-admin.settings.edit', [
            'network' => $network,
            'settings' => $settings,
        ]);
    }

    /**
     * Atualiza configurações da rede
     * Permite editar apenas identidade da rede (nome, descrição, logo, cores, texto público)
     */
    public function update(Request $request)
    {
        $network = app('currentNetwork');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:clinic_networks,slug,' . $network->id],
            'is_active' => ['boolean'],
            'public_description' => ['nullable', 'string', 'max:2000'],
            'public_text' => ['nullable', 'string'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Atualiza dados básicos
        $network->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'is_active' => $request->has('is_active'),
        ]);

        // Processa upload de logo
        if ($request->hasFile('logo')) {
            // Remove logo antiga se existir
            if ($network->settings && isset($network->settings['logo_path'])) {
                Storage::disk('public')->delete($network->settings['logo_path']);
            }

            $logoPath = $request->file('logo')->store('network-logos', 'public');
            $validated['logo_path'] = $logoPath;
        }

        // Atualiza settings (JSON)
        $settings = $network->settings ?? [];
        $settings['public_description'] = $validated['public_description'] ?? null;
        $settings['public_text'] = $validated['public_text'] ?? null;
        $settings['primary_color'] = $validated['primary_color'] ?? null;
        $settings['secondary_color'] = $validated['secondary_color'] ?? null;
        if (isset($validated['logo_path'])) {
            $settings['logo_path'] = $validated['logo_path'];
        }

        $network->update(['settings' => $settings]);

        return redirect()
            ->route('network.settings.edit')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}

