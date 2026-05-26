<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('municipal');
});

Route::get('/cases/new', function () {
    return view('cases.new');
});

use App\Models\CitizenCase;
use App\Models\OutboxEvent;
use App\Mail\CaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::post('/admin/login', function (Request $request) {
    $username = env('ADMIN_USERNAME', 'admin');
    $password = env('ADMIN_PASSWORD', 'password');

    if ($request->input('username') === $username && $request->input('password') === $password) {
        $request->session()->put('is_admin', true);
        return redirect('/admin/cases');
    }

    return redirect('/admin/login')->with('error', 'שם משתמש או סיסמה שגויים');
});

Route::post('/admin/logout', function (Request $request) {
    $request->session()->forget('is_admin');
    return redirect('/');
});

Route::middleware(['admin.auth'])->group(function () {
    Route::get('/admin/cases', function () {
        $cases = CitizenCase::orderBy('created_at', 'desc')->limit(200)->get();
        return view('admin.cases.index', compact('cases'));
    });

    Route::get('/admin/cases/{case}', function (CitizenCase $case) {
        return view('admin.cases.show', compact('case'));
    });

    Route::post('/admin/cases/{case}/status', function (Request $request, CitizenCase $case) {
        $request->validate([
            'status' => ['required', 'string'],
        ]);

        $old = $case->status;
        $case->status = $request->input('status');
        $case->save();

        // send email directly from PHP when status changed
        if ($old !== $case->status && !empty($case->contact_email)) {
            $subject = 'עדכון סטטוס לפנייה #' . $case->id;
            $body = "שלום {$case->applicant_name},\n\nסטטוס הפנייה שלך עודכן ל: {$case->status}.\nמספר מעקב: {$case->id}.\n\nתודה, עיריית ירושלים";

            try {
                Mail::to($case->contact_email)->send(new CaseNotification([
                    'subject' => $subject,
                    'body' => $body,
                    'case' => $case->toArray(),
                ]));
                Log::info('Status change email sent directly', ['case_id' => $case->id, 'to' => $case->contact_email]);
            } catch (\Throwable $e) {
                Log::error('Direct status change email failed, falling back to outbox', ['case_id' => $case->id, 'error' => $e->getMessage()]);

                OutboxEvent::create([
                    'aggregate_type' => 'CitizenCase',
                    'aggregate_id' => $case->id,
                    'event_type' => 'CitizenCaseStatusChanged',
                    'payload' => [
                        'case_id' => $case->id,
                        'old_status' => $old,
                        'new_status' => $case->status,
                        'notifications' => [
                            [
                                'channel' => 'email',
                                'to' => $case->contact_email,
                                'subject' => $subject,
                                'body' => $body,
                                'case' => $case->toArray(),
                            ],
                        ],
                    ],
                ]);
            }
        }

        return redirect("/admin/cases/{$case->id}")->with('success', 'סטטוס התעדכן בהצלחה');
    });
});
