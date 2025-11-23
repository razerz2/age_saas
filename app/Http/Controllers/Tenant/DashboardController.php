<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ✅ Pega o usuário autenticado do guard tenant
        $user = Auth::guard('tenant')->user();

        // ✅ Garante que estamos conectados ao banco da tenant
        $database = config('database.connections.tenant.database');

        return view('tenant.dashboard', compact('user', 'database'));
    }
}
