<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    // Daftar audit log terbaru, hanya untuk admin (dibatasi di routes/api.php).
    public function index(): JsonResponse
    {
        $logs = AuditLog::with('user:id,name,role')
            ->latest('id')
            ->paginate(15);

        return response()->json($logs);
    }
}