<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicamentController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use App\models\User;
use Barryvdh\DomPDF\Facade\Pdf;


// public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('login');

// protected
Route::middleware('auth:sanctum')->group(function () {
// Route::group([], function () {
    Route::get('/medicaments', [MedicamentController::class, 'index']);
    Route::post('/medicaments', [MedicamentController::class, 'store']);
    Route::put('/medicaments/{id}', [MedicamentController::class, 'update']);
    Route::delete('/medicaments/{id}', [MedicamentController::class, 'destroy']);

    Route::post('/ventes', [MedicamentController::class, 'vente']);
    Route::get('/ventes', function () {
        return \App\Models\Vente::with('details.medicament',)->get();
    });
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    


    Route::post('/logout', function (Illuminate\Http\Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    });

   

    

    Route::get('/dashboard', function () {

        $totalRevenue = \App\Models\Vente::sum('total');
        $totalVentes = \App\Models\Vente::count();

        $lowStock = \App\Models\Medicament::where('quantite', '<', 5)->get();

        $ventes = \App\Models\Vente::with('details.medicament')
            ->latest()
            ->take(5)
            ->get();

        foreach ($ventes as $v) {
            $user = User::find($v->user_id);
            $v->user_name = $user ? $user->name : "Unknown";
        }

        return response()->json([
            'totalRevenue' => $totalRevenue,
            'totalVentes' => $totalVentes,
            'lowStock' => $lowStock,
            'ventes' => $ventes
        ]);

    });
       

    Route::get('/stats', function () {

        $ventes = \App\Models\Vente::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->get();

        $stocks = \App\Models\Medicament::select('nom', 'quantite')->get();

        return response()->json([
            'ventes' => $ventes,
            'stocks' => $stocks
        ]);

    });

    

    Route::get('/report/pdf', function () {

        $totalRevenue = \App\Models\Vente::sum('total');
        $totalVentes = \App\Models\Vente::count();
        $lowStock = \App\Models\Medicament::where('quantite', '<', 5)->get();

        $pdf = Pdf::loadView('report', [
            'revenue' => $totalRevenue,
            'ventes' => $totalVentes,
            'lowStock' => $lowStock,
            'pharmacy' => 'Pharma Saad'
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
