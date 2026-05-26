<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\CitizenCase;
// use App\Models\OutboxEvent;
// use App\Models\Idempotency;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Http\Request;

// class CaseController extends Controller
// {
//     public function store(Request $request)
//     {
//         $data = $request->validate([
//             'applicant.name' => 'required|string',
//             'applicant.national_id' => 'nullable|string',
//             'contact.email' => 'nullable|email',
//             'contact.phone' => 'nullable|string',
//             'subject' => 'required|string',
//             'description' => 'nullable|string',
//         ]);

//         $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('idempotency_key');

//         if ($idempotencyKey) {
//             $existing = Idempotency::find($idempotencyKey);
//             if ($existing) {
//                 return response()->json([
//                     'tracking_id' => $existing->tracking_id,
//                     'status' => 'queued',
//                 ], 200);
//             }
//         }

//         $result = DB::transaction(function () use ($data, $idempotencyKey) {
//             $case = CitizenCase::create([
//                 'applicant_name' => $data['applicant']['name'],
//                 'applicant_national_id' => $data['applicant']['national_id'] ?? null,
//                 'contact_email' => $data['contact']['email'] ?? null,
//                 'contact_phone' => $data['contact']['phone'] ?? null,
//                 'subject' => $data['subject'],
//                 'description' => $data['description'] ?? null,
//                 'status' => 'NEW',
//             ]);

//             // write outbox event in same transaction
//             OutboxEvent::create([
//                 'aggregate_type' => 'CitizenCase',
//                 'aggregate_id' => $case->id,
//                 'event_type' => 'CitizenCaseCreated',
//                 'payload' => [
//                     'tracking_id' => $case->id,
//                     'applicant_name' => $case->applicant_name,
//                     'subject' => $case->subject,
//                 ],
//             ]);

//             if ($idempotencyKey) {
//                 Idempotency::create([
//                     'key' => $idempotencyKey,
//                     'tracking_id' => $case->id,
//                     'response_payload' => ['tracking_id' => $case->id, 'status' => 'queued'],
//                 ]);
//             }

//             return $case;
//         });

//         return response()->json([
//             'tracking_id' => $result->id,
//             'status' => 'queued',
//         ], 202);
//     }

//     public function show(CitizenCase $case)
//     {
//         return response()->json([
//             'tracking_id' => $case->id,
//             'status' => $case->status,
//             'created_at' => $case->created_at,
//             'updated_at' => $case->updated_at,
//         ]);
//     }
// }
