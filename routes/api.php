<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BackupController;


// public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('login');
Route::get('/backup', [BackupController::class, 'backup']);

// protected
Route::middleware('auth:sanctum')->group(function () {
// Route::group([], function () {
    Route::get('/medicaments', [MedicamentController::class, 'index']);
    Route::post('/medicaments', [MedicamentController::class, 'store']);
    Route::put('/medicaments/{id}', [MedicamentController::class, 'update']);
    Route::delete('/medicaments/{id}', [MedicamentController::class, 'destroy']);

    Route::post('/ventes', [MedicamentController::class, 'vente']);
    Route::get('/ventes', function () {
        return \App\Models\Vente::with('details.medicament')->latest()->get();
    });
    Route::get('/users', function () {
        return \App\Models\User::all();
    });
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::apiResource('users', UserController::class);
    


    Route::post('/logout', function (Illuminate\Http\Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    });

   

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/stats', [DashboardController::class, 'stats']);

    

    Route::get('/report/pdf', function (Request $request) {
        $days = $request->get('days') === 'all' ? 'all' : (int) $request->get('days', 30);
        $days = in_array($days, ['all', 7, 14, 30], true) ? $days : 30;
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $periodLabel = $request->get('period_label');

        if (!$periodLabel) {
            $periodLabel = $days === 'all'
                ? 'Toute la periode'
                : now()->subDays($days)->format('Y-m-d') . ' au ' . now()->format('Y-m-d');
        }

        $ventesQuery = \App\Models\Vente::query();

        if ($days !== 'all') {
            $ventesQuery->where('created_at', '>=', now()->subDays($days));
        }

        $totalRevenue = (clone $ventesQuery)->sum('total');
        $totalVentes = (clone $ventesQuery)->count();
        $lowStock = \App\Models\Medicament::where('quantite', '<', 5)->get();

        $pdf = Pdf::loadView('report', [
            'revenue' => $totalRevenue,
            'ventes' => $totalVentes,
            'lowStock' => $lowStock,
            'pharmacy' => 'Pharma Saad',
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->download('report.pdf');
    });

    

    

});

// });
// Route::get('/dashboard', function () {

//     $user = auth()->user();

//     if (!$user || $user->role !== 'admin') {
//         return response()->json(['error' => 'Unauthorized'], 403);
//     }

//     $totalVentes = \App\Models\Vente::count();
//     $totalRevenue = \App\Models\Vente::sum('total');
//     $lowStock = \App\Models\Medicament::where('quantite', '<=', 5)->get();

//     return response()->json([
//         'totalVentes' => $totalVentes,
//         'totalRevenue' => $totalRevenue,
//         'lowStock' => $lowStock
//     ]);

// });
