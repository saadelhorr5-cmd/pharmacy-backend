<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicament;

class MedicamentController extends Controller
{
    // GET all
    public function index()
    {
        return response()->json(Medicament::all());
    }

    // POST
    public function store(Request $request)
{
    return Medicament::create($request->all());
}

    // GET by id
    public function show($id)
    {
        return Medicament::findOrFail($id);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $med = Medicament::findOrFail($id);
        $med->update($request->all());
        return $med;
    }

    // DELETE
    public function destroy($id)
    {
        Medicament::destroy($id);
        return response()->json(['message' => 'deleted']);
    }

    // vendre 
    public function vente(Request $request)
    {
        $cart = $request->input('cart');

        foreach ($cart as $item) {
            $med = Medicament::find($item['id']);

            if ($med) {

                // ❗ check stock
                if ($med->quantite >= $item['quantite']) {
                    $med->quantite -= $item['quantite'];
                    $med->save();
                } else {
                    return response()->json([
                        'error' => 'Stock insuffisant pour ' . $med->nom
                    ], 400);
                }
            }
        }

        return response()->json(['message' => 'Vente réussie']);
    }



}