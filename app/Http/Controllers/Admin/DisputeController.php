<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * Display a listing of disputes.
     */
    public function index(Request $request)
    {
        $query = Dispute::with(['trader', 'analyst', 'subscription']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('trader', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('analyst', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $disputes = $query->latest()->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    /**
     * Display the specified dispute.
     */
    public function show($id)
    {
        $dispute = Dispute::with(['trader', 'analyst', 'subscription'])->findOrFail($id);
        
        return view('admin.disputes.show', compact('dispute'));
    }

    /**
     * Resolve the dispute.
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution' => 'required|in:refund,dismiss,warning',
            'admin_notes' => 'required|string',
        ]);

        $dispute = Dispute::findOrFail($id);

        $dispute->update([
            'status' => 'resolved',
            'resolution' => $request->resolution,
            'admin_notes' => $request->admin_notes,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        // Logic based on resolution
        if ($request->resolution === 'refund') {
            // TODO: Trigger refund logic (Stripe/Chapa)
            // For now, we assume manual refund or just marking it
            if ($dispute->subscription) {
                $dispute->subscription->update(['status' => 'cancelled', 'cancelled_at' => now(), 'cancelled_by' => 'admin']);
            }
        }

        // Notify Trader (Email + DB)
        $trader = $dispute->trader;
        if ($trader) {
            \App\Models\Notification::create([
                'user_id' => $trader->id,
                'type' => 'dispute_resolved',
                'title' => 'Dispute Resolved',
                'message' => "Your dispute #{$dispute->id} has been resolved: " . ucfirst($request->resolution),
                'data' => json_encode(['dispute_id' => $dispute->id]),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($trader->email)->send(new \App\Mail\DisputeResolvedMail($dispute));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send dispute resolved email: ' . $e->getMessage());
            }
        }

        // Notify Analyst (DB Only)
        $analyst = $dispute->analyst;
        if ($analyst) {
             \App\Models\Notification::create([
                'user_id' => $analyst->id,
                'type' => 'dispute_resolved',
                'title' => 'Dispute Resolved',
                'message' => "Dispute #{$dispute->id} has been resolved by admin.",
                'data' => json_encode(['dispute_id' => $dispute->id]),
            ]);
        }

        return redirect()->route('admin.disputes.show', $dispute->id)
            ->with('success', 'Dispute resolved successfully.');
    }
}
