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
        $dateRange = $this->getDateRange($request);

        $ventesQuery = Vente::with(['details.medicament', 'user'])->latest();

        if ($dateRange) {
            $ventesQuery->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        } elseif ($days !== 'all') {
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
        $dateRange = $this->getDateRange($request);

        $ventesQuery = Vente::query();

        if ($dateRange) {
            $ventesQuery->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        } elseif ($days !== 'all') {
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

        return in_array($days, [1, 7, 14, 30], true) ? $days : 30;
    }

    private function getDateRange(Request $request): ?array
    {
        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            return null;
        }

        $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->get('end_date'))->endOfDay();

        if ($startDate->greaterThan($endDate)) {
            return null;
        }

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }
}
