<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CaseController extends BaseController
{
    public function store(Request $request)
    {
        // Example: validate, create Case model, write outbox entry
        $data = $request->only(['applicant', 'subject', 'description', 'contact']);
        // In a real Laravel app: dispatch job to publish outbox event
        return response()->json([
            'tracking_id' => 'sample-tracking-id',
            'status' => 'queued'
        ], 202);
    }

    public function show($id)
    {
        // Example: fetch from DB and return status
        return response()->json([
            'tracking_id' => $id,
            'status' => 'IN_PROGRESS',
            'last_updated' => now()
        ], 200);
    }
}
