<?php

namespace App\Http\Controllers\Tenant\PatientPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        return view('tenant.patient_portal.profile.index');
    }

    public function update(Request $request)
    {
        // Implementação real de atualização de perfil pode ser adicionada depois.
        return redirect()->back()->with('success', 'Perfil atualizado.');
    }
}
