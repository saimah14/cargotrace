<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Daftar user (khusus admin, dijaga di routes/api.php).
    public function index(): JsonResponse
    {
        $users = User::with([
            'shipments:id,customer_id,shipment_number',
            'shipments.exceptions:id,shipment_id,type,severity,status,detected_at',
        ])->orderBy('id')->get(['id', 'name', 'email', 'role', 'created_at']);

        $users->each(function (User $user) {
            $exceptions = $user->shipments->flatMap(function ($shipment) {
                return $shipment->exceptions->map(function ($exception) use ($shipment) {
                    return [
                        'shipment_number' => $shipment->shipment_number,
                        'type' => $exception->type,
                        'severity' => $exception->severity,
                        'status' => $exception->status,
                        'detected_at' => $exception->detected_at,
                    ];
                });
            })->values();

            $user->setAttribute('exceptions', $exceptions);
            $user->makeHidden('shipments');
        });

        return response()->json($users);
    }

    // Ubah role user (khusus admin).
    public function updateRole(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'operator', 'customer'])],
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot change your own role.'], 422);
        }

        $from = $user->role;
        $user->role = $data['role'];
        $user->save();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user.role_changed',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'changes' => ['from' => $from, 'to' => $data['role']],
        ]);

        return response()->json($user->only(['id', 'name', 'email', 'role']));
    }
}