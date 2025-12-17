<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\DoctorBillingPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceSettingsController extends Controller
{
    /**
     * Exibe a página de configurações financeiras
     */
    public function index()
    {
        // Permitir acesso mesmo se módulo não estiver habilitado (para habilitar pela primeira vez)

        $settings = [
            'finance.enabled' => tenant_setting('finance.enabled', 'false'),
            'finance.payment_provider' => tenant_setting('finance.payment_provider', 'asaas'),
            'finance.asaas.environment' => tenant_setting('finance.asaas.environment', 'sandbox'),
            'finance.asaas.api_key' => tenant_setting('finance.asaas.api_key', ''),
            'finance.asaas.webhook_secret' => tenant_setting('finance.asaas.webhook_secret', ''),
            'finance.billing_mode' => tenant_setting('finance.billing_mode', 'disabled'),
            'finance.global_billing_type' => tenant_setting('finance.global_billing_type', 'reservation'),
            'finance.charge_on_public_appointment' => tenant_setting('finance.charge_on_public_appointment', 'false') === 'true',
            'finance.charge_on_patient_portal' => tenant_setting('finance.charge_on_patient_portal', 'false') === 'true',
            'finance.charge_on_internal_appointment' => tenant_setting('finance.charge_on_internal_appointment', 'false') === 'true',
            'finance.reservation_amount' => tenant_setting('finance.reservation_amount', '0.00'),
            'finance.full_appointment_amount' => tenant_setting('finance.full_appointment_amount', '0.00'),
            'finance.payment_methods' => json_decode(tenant_setting('finance.payment_methods', '["pix"]'), true) ?? ['pix'],
            'finance.auto_send_payment_link' => tenant_setting('finance.auto_send_payment_link', 'true') === 'true',
            'finance.default_account_id' => tenant_setting('finance.default_account_id', null),
            'finance.doctor_commission_enabled' => tenant_setting('finance.doctor_commission_enabled', 'false') === 'true',
            'finance.default_commission_percentage' => tenant_setting('finance.default_commission_percentage', '0'),
        ];

        return view('tenant.settings.finance', compact('settings'));
    }

    /**
     * Atualiza as configurações financeiras
     */
    public function update(Request $request)
    {
        // Permitir atualização mesmo se módulo não estiver habilitado (para habilitar pela primeira vez)

        $request->validate([
            'finance_enabled' => 'nullable|boolean',
            'payment_provider' => 'required|in:asaas',
            'asaas_environment' => 'required|in:sandbox,production',
            'asaas_api_key' => 'required_if:payment_provider,asaas|string',
            'asaas_webhook_secret' => 'nullable|string',
            'billing_mode' => 'required|in:disabled,global,per_doctor,per_doctor_specialty',
            'global_billing_type' => 'nullable|in:reservation,full',
            'charge_on_public_appointment' => 'nullable|boolean',
            'charge_on_patient_portal' => 'nullable|boolean',
            'charge_on_internal_appointment' => 'nullable|boolean',
            'reservation_amount' => 'nullable|numeric|min:0',
            'full_appointment_amount' => 'nullable|numeric|min:0',
            'billing_prices' => 'nullable|array',
            'removed_prices' => 'nullable|string',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:pix,credit_card,boleto',
            'auto_send_payment_link' => 'nullable|boolean',
            'default_account_id' => 'nullable|uuid|exists:financial_accounts,id',
            'doctor_commission_enabled' => 'nullable|boolean',
            'default_commission_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Atualizar configurações
        if ($request->has('finance_enabled')) {
            TenantSetting::set('finance.enabled', $request->finance_enabled ? 'true' : 'false');
        }

        TenantSetting::set('finance.payment_provider', $request->payment_provider);
        TenantSetting::set('finance.asaas.environment', $request->asaas_environment);
        TenantSetting::set('finance.asaas.api_key', $request->asaas_api_key ?? '');
        
        // Gerar webhook secret se não existir ou se solicitado
        if ($request->has('regenerate_webhook_secret') || !tenant_setting('finance.asaas.webhook_secret')) {
            TenantSetting::set('finance.asaas.webhook_secret', Str::random(64));
        } elseif ($request->has('asaas_webhook_secret')) {
            TenantSetting::set('finance.asaas.webhook_secret', $request->asaas_webhook_secret);
        }

        TenantSetting::set('finance.billing_mode', $request->billing_mode);
        
        // Salvar tipo de cobrança global se aplicável
        if ($request->billing_mode === 'global' && $request->has('global_billing_type')) {
            TenantSetting::set('finance.global_billing_type', $request->global_billing_type);
        }
        
        TenantSetting::set('finance.charge_on_public_appointment', $request->has('charge_on_public_appointment') ? 'true' : 'false');
        TenantSetting::set('finance.charge_on_patient_portal', $request->has('charge_on_patient_portal') ? 'true' : 'false');
        TenantSetting::set('finance.charge_on_internal_appointment', $request->has('charge_on_internal_appointment') ? 'true' : 'false');
        TenantSetting::set('finance.reservation_amount', $request->reservation_amount ?? '0.00');
        TenantSetting::set('finance.full_appointment_amount', $request->full_appointment_amount ?? '0.00');
        TenantSetting::set('finance.payment_methods', json_encode($request->payment_methods ?? ['pix']));
        TenantSetting::set('finance.auto_send_payment_link', $request->has('auto_send_payment_link') ? 'true' : 'false');
        TenantSetting::set('finance.default_account_id', $request->default_account_id ?? null);
        TenantSetting::set('finance.doctor_commission_enabled', $request->has('doctor_commission_enabled') ? 'true' : 'false');
        TenantSetting::set('finance.default_commission_percentage', $request->default_commission_percentage ?? '0');

        // Processar preços por médico/especialidade
        if (in_array($request->billing_mode, ['per_doctor', 'per_doctor_specialty']) && $request->has('billing_prices')) {
            $this->saveDoctorBillingPrices($request->billing_prices, $request->billing_mode, $request->removed_prices);
        }

        return redirect()->route('tenant.settings.finance.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Configurações financeiras atualizadas com sucesso.');
    }

    /**
     * Salva os preços de cobrança por médico/especialidade
     */
    private function saveDoctorBillingPrices(array $prices, string $mode, ?string $removedPrices): void
    {
        try {
            DB::beginTransaction();

            // Remover preços marcados para remoção
            if ($removedPrices) {
                $removedIds = explode(',', $removedPrices);
                DoctorBillingPrice::whereIn('id', $removedIds)->update(['active' => false]);
            }

            // Processar preços por médico
            if ($mode === 'per_doctor') {
                foreach ($prices as $doctorId => $priceData) {
                    $this->savePrice($doctorId, null, $priceData);
                }
            } 
            // Processar preços por médico e especialidade
            elseif ($mode === 'per_doctor_specialty') {
                foreach ($prices as $doctorId => $specialties) {
                    foreach ($specialties as $specialtyId => $priceData) {
                        $this->savePrice($doctorId, $specialtyId, $priceData);
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao salvar preços por médico/especialidade: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Salva um preço individual
     */
    private function savePrice(string $doctorId, ?string $specialtyId, array $priceData): void
    {
        $type = $priceData['type'] ?? 'reservation';
        
        // Define os valores baseado no tipo: apenas um pode ter valor > 0
        if ($type === 'reservation') {
            $reservationAmount = (float) ($priceData['reservation_amount'] ?? 0);
            $fullAmount = 0; // Sempre zero quando é reserva
        } else { // full
            $reservationAmount = 0; // Sempre zero quando é valor completo
            $fullAmount = (float) ($priceData['full_appointment_amount'] ?? 0);
        }

        // Se o valor do tipo selecionado é zero, não criar/atualizar
        if ($reservationAmount == 0 && $fullAmount == 0) {
            // Desativar se existir
            DoctorBillingPrice::where('doctor_id', $doctorId)
                ->where('specialty_id', $specialtyId)
                ->update(['active' => false]);
            return;
        }

        // Buscar ou criar
        $price = DoctorBillingPrice::where('doctor_id', $doctorId)
            ->where('specialty_id', $specialtyId)
            ->first();

        if ($price) {
            $price->update([
                'reservation_amount' => $reservationAmount,
                'full_appointment_amount' => $fullAmount,
                'active' => true,
            ]);
        } else {
            DoctorBillingPrice::create([
                'doctor_id' => $doctorId,
                'specialty_id' => $specialtyId,
                'reservation_amount' => $reservationAmount,
                'full_appointment_amount' => $fullAmount,
                'active' => true,
            ]);
        }
    }
}

