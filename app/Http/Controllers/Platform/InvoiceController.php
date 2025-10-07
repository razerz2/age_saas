<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoices::with(['tenant', 'subscription'])->orderBy('created_at', 'desc')->get();
        return view('platform.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $subscriptions = Subscription::with('tenant')->get();
        $tenants = Tenant::all();
        return view('platform.invoices.create', compact('subscriptions', 'tenants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'subscription_id' => 'required|uuid|exists:subscriptions,id',
            'amount_cents' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue,canceled',
            'payment_link' => 'nullable|string|max:255',
            'provider' => 'nullable|string|max:100',
            'provider_id' => 'nullable|string|max:100',
        ]);

        Invoices::create($data);

        return redirect()->route('Platform.invoices.index')->with('success', 'Fatura criada com sucesso!');
    }

    public function edit(Invoices $invoice)
    {
        $subscriptions = Subscription::with('tenant')->get();
        $tenants = Tenant::all();
        return view('platform.invoices.edit', compact('invoice', 'subscriptions', 'tenants'));
    }

    public function update(Request $request, Invoices $invoice)
    {
        $data = $request->validate([
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'subscription_id' => 'required|uuid|exists:subscriptions,id',
            'amount_cents' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,paid,overdue,canceled',
            'payment_link' => 'nullable|string|max:255',
            'provider' => 'nullable|string|max:100',
            'provider_id' => 'nullable|string|max:100',
        ]);

        $invoice->update($data);

        return redirect()->route('Platform.invoices.index')->with('success', 'Fatura atualizada com sucesso!');
    }

    public function show(Invoices $invoice)
    {
        return view('platform.invoices.show', compact('invoice'));
    }

    public function destroy(Invoices $invoice)
    {
        $invoice->delete();
        return redirect()->route('Platform.invoices.index')->with('success', 'Fatura excluída com sucesso!');
    }
}
