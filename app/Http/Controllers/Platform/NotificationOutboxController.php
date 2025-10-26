<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\NotificationOutbox;
use App\Models\Platform\Tenant;
use App\Http\Requests\NotificationOutboxRequest;

class NotificationOutboxController extends Controller
{
    public function index()
    {
        $notifications = NotificationOutbox::orderBy('created_at')->get();

        return view('platform.notifications_outbox.index', compact('notifications'));
    }

    public function create()
    {
        $tenants = Tenant::select('id', 'legal_name')->get();
        return view('platform.notifications_outbox.create', compact('tenants'));
    }

    public function store(NotificationOutboxRequest $request)
    {
        $validated = $request->validate();

        $validated['meta'] = $validated['meta'] ?? [];

        NotificationOutbox::create($validated);

        return redirect()->route('Platform.notifications_outbox.index')->with('success', 'Notificação criada com sucesso.');
    }

    public function show(NotificationOutbox $notificationOutbox)
    {
        return view('platform.notifications_outbox.show', compact('notificationOutbox'));
    }

    public function edit(NotificationOutbox $notificationOutbox)
    {
        $tenants = Tenant::select('id', 'name')->get();
        return view('platform.notifications_outbox.edit', compact('notificationOutbox', 'tenants'));
    }

    public function update(NotificationOutboxRequest $request, NotificationOutbox $notificationOutbox)
    {
        $validated = $request->validate();

        $notificationOutbox->update($validated);

        return redirect()->route('Platform.notifications_outbox.index')->with('success', 'Notificação atualizada com sucesso.');
    }

    public function destroy(NotificationOutbox $notificationOutbox)
    {
        $notificationOutbox->delete();
        return redirect()->route('Platform.notifications_outbox.index')->with('success', 'Notificação excluída com sucesso.');
    }
}