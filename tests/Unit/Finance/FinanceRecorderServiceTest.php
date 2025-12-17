<?php

namespace Tests\Unit\Finance;

use Tests\TestCase;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\FinancialTransaction;
use App\Services\Finance\FinanceRecorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Testes unitários para FinanceRecorderService
 * 
 * Cenários testados:
 * 1. Finance sem Billing - Appointment cria FinancialTransaction
 * 2. Finance com Billing - PaymentConfirmed cria FinancialTransaction
 * 3. Pagamento parcial - Múltiplas transações vinculadas à mesma charge
 */
class FinanceRecorderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar tenant para testes
        $this->tenant = \App\Models\Platform\Tenant::factory()->create();
        $this->actingAs($this->tenant->users()->first(), 'tenant');
    }

    /**
     * Cenário 1: Finance sem Billing
     * 
     * Appointment cria FinancialTransaction
     * - gross_amount = net_amount
     * - gateway_fee = 0
     * - origin_type = 'appointment'
     * - direction = 'credit'
     */
    public function test_record_appointment_income_without_billing(): void
    {
        // Arrange
        $appointment = Appointment::factory()->create([
            'starts_at' => now()->addDay(),
        ]);
        
        // Configurar valores globais
        tenant_setting('finance.enabled', 'true');
        tenant_setting('finance.billing_mode', 'global');
        tenant_setting('finance.global_billing_type', 'full');
        tenant_setting('finance.full_appointment_amount', '200.00');
        
        $service = app(FinanceRecorderService::class);
        
        // Act
        $transaction = $service->recordAppointmentIncome($appointment);
        
        // Assert
        $this->assertInstanceOf(FinancialTransaction::class, $transaction);
        $this->assertEquals('income', $transaction->type);
        $this->assertEquals('appointment', $transaction->origin_type);
        $this->assertEquals($appointment->id, $transaction->origin_id);
        $this->assertEquals('credit', $transaction->direction);
        $this->assertEquals(200.00, (float) $transaction->gross_amount);
        $this->assertEquals(0.00, (float) $transaction->gateway_fee);
        $this->assertEquals(200.00, (float) $transaction->net_amount);
        $this->assertEquals(200.00, (float) $transaction->amount);
        $this->assertEquals('paid', $transaction->status);
    }

    /**
     * Cenário 2: Finance com Billing (via PaymentConfirmed)
     * 
     * PaymentConfirmed cria FinancialTransaction
     * - gross_amount > net_amount
     * - gateway_fee >= 0
     * - origin_type = 'charge'
     */
    public function test_create_transaction_from_payment_confirmed_with_fee(): void
    {
        // Arrange
        $charge = \App\Models\Tenant\FinancialCharge::factory()->create([
            'amount' => 200.00,
            'status' => 'pending',
        ]);
        
        $charge->load(['appointment', 'patient']);
        
        $grossAmount = 200.00;
        $gatewayFee = 7.98; // Taxa de cartão de crédito (3,99% + R$ 0,40)
        $netAmount = $grossAmount - $gatewayFee;
        
        $event = new \App\Events\Finance\PaymentConfirmed(
            $charge,
            'credit_card',
            'test-event-id',
            $grossAmount,
            $gatewayFee
        );
        
        $listener = new \App\Listeners\Finance\CreateTransactionOnPaymentConfirmed();
        
        // Act
        $listener->handle($event);
        
        // Assert
        $transaction = FinancialTransaction::where('origin_type', 'charge')
            ->where('origin_id', $charge->id)
            ->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals('income', $transaction->type);
        $this->assertEquals('charge', $transaction->origin_type);
        $this->assertEquals($charge->id, $transaction->origin_id);
        $this->assertEquals('credit', $transaction->direction);
        $this->assertEquals($grossAmount, (float) $transaction->gross_amount);
        $this->assertEquals($gatewayFee, (float) $transaction->gateway_fee);
        $this->assertEquals($netAmount, (float) $transaction->net_amount);
        $this->assertEquals($netAmount, (float) $transaction->amount);
        $this->assertEquals('paid', $transaction->status);
    }

    /**
     * Cenário 3: Pagamento parcial
     * 
     * Duas transações vinculadas à mesma charge
     * - Status da charge = partially_paid
     */
    public function test_partial_payment_creates_multiple_transactions(): void
    {
        // Arrange
        $charge = \App\Models\Tenant\FinancialCharge::factory()->create([
            'amount' => 200.00,
            'status' => 'pending',
        ]);
        
        $charge->load(['appointment', 'patient']);
        
        // Primeiro pagamento parcial
        $event1 = new \App\Events\Finance\PaymentConfirmed(
            $charge,
            'pix',
            'event-1',
            100.00, // gross_amount
            0.00    // gateway_fee
        );
        
        $listener = new \App\Listeners\Finance\CreateTransactionOnPaymentConfirmed();
        
        // Act - Primeiro pagamento
        $listener->handle($event1);
        
        // Assert - Primeira transação
        $transaction1 = FinancialTransaction::where('origin_type', 'charge')
            ->where('origin_id', $charge->id)
            ->first();
        
        $this->assertNotNull($transaction1);
        $this->assertEquals(100.00, (float) $transaction1->net_amount);
        
        // Verificar status da charge
        $charge->refresh();
        $this->assertEquals('partially_paid', $charge->payment_status);
        $this->assertEquals(100.00, (float) $charge->paid_amount);
        
        // Segundo pagamento parcial
        $event2 = new \App\Events\Finance\PaymentConfirmed(
            $charge,
            'pix',
            'event-2',
            100.00, // gross_amount
            0.00    // gateway_fee
        );
        
        // Act - Segundo pagamento
        $listener->handle($event2);
        
        // Assert - Duas transações
        $transactions = FinancialTransaction::where('origin_type', 'charge')
            ->where('origin_id', $charge->id)
            ->get();
        
        $this->assertCount(2, $transactions);
        
        $totalPaid = $transactions->sum('net_amount');
        $this->assertEquals(200.00, (float) $totalPaid);
        
        // Verificar status da charge após segundo pagamento
        $charge->refresh();
        $this->assertEquals('paid', $charge->payment_status);
        $this->assertEquals(200.00, (float) $charge->paid_amount);
    }
}

