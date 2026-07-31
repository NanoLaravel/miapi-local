<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlanAccessHelper;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::query()->where('owner_id', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderByDesc('created_at')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'place_id' => 'nullable|exists:places,id',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date',
            'total_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::findOrFail($request->lead_id);

        abort_unless($lead->owner_id === Auth::id(), 403);

        if (!PlanAccessHelper::canManageReservations((int) $lead->owner_id)) {
            return response()->json([
                'message' => 'El negocio no tiene un plan activo que permita gestionar reservas.',
            ], 403);
        }

        $reservation = Reservation::create([
            'lead_id' => $lead->id,
            'place_id' => $request->place_id,
            'user_id' => $lead->user_id,
            'owner_id' => $lead->owner_id,
            'reservation_code' => 'RES-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'nights' => $request->nights ?? 1,
            'total_amount' => $request->total_amount ?? 0,
            'currency' => $request->currency ?? 'COP',
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        $lead->update(['status' => 'interested']);

        return response()->json($reservation, 201);
    }

    public function show(Reservation $reservation)
    {
        abort_unless($reservation->owner_id === Auth::id(), 403);

        return response()->json($reservation);
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->owner_id === Auth::id(), 403);

        $request->validate([
            'status' => 'required|in:pending,confirmed,paid,cancelled,rejected',
        ]);

        $reservation->update(['status' => $request->status]);

        return response()->json($reservation);
    }
}
