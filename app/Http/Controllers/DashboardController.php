<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Vente;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = $request->get('days', 30); // default 30

        $from = Carbon::now()->subDays($days);

        $ventes = Vente::with(['details.medicament', 'user'])
            ->where('created_at', '>=', $from)
            ->get();

        return response()->json([
            "totalRevenue" => $ventes->sum('total'),
            "totalVentes" => $ventes->count(),
            "lowStock" => \App\Models\Medicament::where('quantite', '<', 5)->get(),

            "ventes" => $ventes->map(function($v){
                return [
                    "id" => $v->id,
                    "total" => $v->total,
                    "user_name" => $v->user->name,
                    "details" => $v->details
                ];
            })
        ]);
    }  

    public function stats(Request $request)
    {
        $days = $request->get('days', 30);

        $from = Carbon::now()->subDays($days);

        $ventes = Vente::where('created_at', '>=', $from)->get();

        $data = $ventes->groupBy(function($v){
            return $v->created_at->format('Y-m-d');
        });

        return response()->json([
            'ventes' => collect($data)->map(function($v, $date){
                return [
                    'date' => $date,
                    'total' => $v->sum('total')
                ];
            })->values()
        ]);
    }
}