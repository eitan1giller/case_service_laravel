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
use Illuminate\Http\Request;

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

        $case->status = $request->input('status');
        $case->save();

        return redirect("/admin/cases/{$case->id}")->with('success', 'סטטוס התעדכן בהצלחה');
    });
});
