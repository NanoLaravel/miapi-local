<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PlanAccessHelper;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = Auth::id();

        $leads = Lead::query()
            ->where('owner_id', $ownerId)
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'leadable_type' => 'required|string',
            'leadable_id' => 'required|integer',
            'contact_type' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        $owner = \App\Models\User::findOrFail($request->owner_id);
        $canReceiveLeads = PlanAccessHelper::canReceiveLeads((int) $owner->id, $request->leadable_type);

        if (!$canReceiveLeads) {
            return response()->json([
                'message' => 'El negocio no tiene un plan activo que permita recibir leads.',
            ], 403);
        }

        $lead = Lead::create([
            'user_id' => Auth::id(),
            'owner_id' => $owner->id,
            'leadable_type' => $request->leadable_type,
            'leadable_id' => $request->leadable_id,
            'contact_type' => $request->contact_type ?? 'whatsapp',
            'message' => $request->message,
            'status' => 'pending',
            'source' => 'app',
            'priority' => 'medium',
        ]);

        return response()->json([
            'message' => 'Lead creado correctamente.',
            'data' => $lead,
        ], 201);
    }

    public function show(Lead $lead)
    {
        abort_unless($lead->owner_id === Auth::id(), 403);

        return response()->json($lead);
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        abort_unless($lead->owner_id === Auth::id(), 403);

        $request->validate([
            'status' => 'required|in:pending,contacted,interested,rejected,converted',
        ]);

        $lead->update(['status' => $request->status]);

        return response()->json($lead);
    }

    protected function canReceiveLeads($owner, string $leadableType): bool
    {
        return PlanAccessHelper::canReceiveLeads((int) $owner->id, $leadableType);
    }
}
