<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Notification;
use Illuminate\Http\Request;

class NotificationReportController extends Controller
{
    public function index()
    {
        return view('tenant.reports.notifications.index');
    }

    public function data(Request $request)
    {
        $query = Notification::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total' => $notifications->count(),
            'read' => $notifications->where('read_at', '!=', null)->count(),
            'unread' => $notifications->where('read_at', null)->count(),
        ];

        $table = $notifications->map(function($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title ?? 'N/A',
                'type' => $notification->type ?? 'N/A',
                'read_at' => $notification->read_at ? $notification->read_at->format('d/m/Y H:i') : 'Não lida',
                'created_at' => $notification->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'summary' => $summary,
            'chart' => [],
            'table' => $table,
        ]);
    }

    public function exportExcel(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportPdf(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
    public function exportCsv(Request $request) { return response()->json(['message' => 'Em desenvolvimento']); }
}

