<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use App\Models\Platform\ImportLog;
use App\Models\Platform\Plan;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Services\Platform\TenantCreatorService;
use App\Jobs\Platform\ImportTenantsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NetworkTenantImportController extends Controller
{
    protected $tenantCreator;

    public function __construct(TenantCreatorService $tenantCreator)
    {
        $this->tenantCreator = $tenantCreator;
    }

    /**
     * Mostra o formulário de importação para uma rede específica
     */
    public function index(ClinicNetwork $network)
    {
        $plans = Plan::where('category', Plan::CATEGORY_CONTRACTUAL)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('platform.clinic-networks.import', compact('network', 'plans'));
    }

    /**
     * Mostra o formulário de importação geral (seleção de rede manual)
     */
    public function generalImport()
    {
        $networks = ClinicNetwork::where('is_active', true)->orderBy('name')->get();
        $plans = Plan::where('category', Plan::CATEGORY_CONTRACTUAL)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('platform.clinic-networks.general-import', compact('networks', 'plans'));
    }

    /**
     * Processa o arquivo de importação e despacha o Job
     */
    public function import(Request $request, ClinicNetwork $network = null)
    {
        $rules = [
            'file'    => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'plan_id' => 'required|exists:plans,id',
        ];

        if (!$network) {
            $rules['network_id'] = 'required|exists:clinic_networks,id';
        }

        $request->validate($rules);

        if (!$network) {
            $network = ClinicNetwork::findOrFail($request->network_id);
        }

        $file = $request->file('file');
        
        // 🔒 Verificar se o arquivo já foi importado (idempotência)
        $fileHash = md5_file($file->getPathname());
        if (ImportLog::where('file_hash', $fileHash)->where('status', 'completed')->exists()) {
            return back()->withErrors(['file' => 'Este arquivo já foi importado anteriormente.']);
        }

        // Salvar arquivo temporariamente para o Job processar
        $tempPath = $file->storeAs('temp-imports', time() . '_' . $file->getClientOriginalName());

        // Criar registro de log pendente
        $importLog = ImportLog::create([
            'file_name' => $file->getClientOriginalName(),
            'file_hash' => $fileHash,
            'type'      => 'tenant_import',
            'user_id'   => Auth::id(),
            'status'    => 'pending',
            'temp_path' => $tempPath,
            'config'    => [
                'network_id' => $network->id,
                'plan_id'    => $request->plan_id,
                'allow_duplicate_document' => $request->has('allow_duplicate_document'),
            ]
        ]);

        // Disparar o Job assíncrono
        ImportTenantsJob::dispatch($importLog);

        return redirect()->route('Platform.import.progress', $importLog->id);
    }

    /**
     * Tela de acompanhamento de progresso
     */
    public function showProgress(ImportLog $importLog)
    {
        return view('platform.clinic-networks.import-progress', compact('importLog'));
    }

    /**
     * API para retornar o status atual da importação (AJAX)
     */
    public function getStatus(ImportLog $importLog)
    {
        return response()->json([
            'status' => $importLog->status,
            'total' => $importLog->total_rows,
            'processed' => $importLog->processed_rows,
            'errors' => $importLog->error_count,
            'skipped' => $importLog->skipped_count,
            'summary' => $importLog->summary,
            'percentage' => $importLog->total_rows > 0 
                ? round(($importLog->processed_rows / $importLog->total_rows) * 100) 
                : 0
        ]);
    }
}
