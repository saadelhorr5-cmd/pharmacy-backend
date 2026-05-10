<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Vente;
use App\Models\Medicament;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = $this->getDays($request);

        $ventesQuery = Vente::with(['details.medicament', 'user'])->latest();

        if ($days !== 'all') {
            $ventesQuery->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $ventes = $ventesQuery->get();

        return response()->json([
            "totalRevenue" => $ventes->sum('total'),
            "totalVentes" => $ventes->count(),
            "lowStock" => Medicament::where('quantite', '<', 5)->get(),

            "ventes" => $ventes->map(function($v){
                return [
                    "id" => $v->id,
                    "total" => $v->total,
                    "user_name" => $v->user ? $v->user->name : "Unknown",
                    "details" => $v->details
                ];
            })
        ]);
    }  

    public function stats(Request $request)
    {
        $days = $this->getDays($request);

        $ventesQuery = Vente::query();

        if ($days !== 'all') {
            $ventesQuery->where('created_at', '>=', Carbon::now()->subDays($days));
        }

        $ventes = $ventesQuery->get();

        $data = $ventes->groupBy(function($v){
            return $v->created_at->format('Y-m-d');
        });

        return response()->json([
            'ventes' => collect($data)->map(function($v, $date){
                return [
                    'date' => $date,
                    'total' => $v->sum('total')
                ];
            })->values(),
            'stocks' => Medicament::select('nom', 'quantite')->get()
        ]);
    }

    private function getDays(Request $request): int|string
    {
        if ($request->get('days') === 'all') {
            return 'all';
        }

        $days = (int) $request->get('days', 30);

        return in_array($days, [7, 14, 30], true) ? $days : 30;
    }
}
