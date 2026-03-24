<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicament;
use App\Models\Vente;
use App\Models\VenteDetail;

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
        $user = auth()->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

       
        
        //  نقلب واش الدواء موجود
        $med = Medicament::where('nom', $request->nom)->first();

        if ($med) {
            //  إلا موجود → نزيد quantity
            $med->quantite += $request->quantite;

            //  نقدر نبدلو حتى الثمن إلا تبدل
            $med->prix = $request->prix;

            //  optional: update image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = strtolower(str_replace(' ', '_', $request->nom)) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images'), $filename);
                $med->image = $filename;
            }

            $med->save();

            return response()->json(['message' => 'Quantité mise à jour']);
        }

        //  إلا ما كاينش → نخلق جديد
        $data = $request->all();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = strtolower(str_replace(' ', '_', $request->nom)) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['image'] = $filename;
        }

        return Medicament::create($data);
    }

    // GET by id
    public function show($id)
    {
        return Medicament::findOrFail($id);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $med = Medicament::find($id);
        $med->update($request->all());

        return $med;

        $med = Medicament::find($id);
        $data = $request->all();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $data['image'] = $filename;
        }

        $med->update($data);

        return response()->json($med);
    }

    // DELETE
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Medicament::destroy($id);
        return response()->json(['message' => 'deleted']);
    }

    // vendre 
    
    public function vente(Request $request)
    {
        $cart = $request->input('items');

        $total = 0;

        foreach ($cart as $item) {
            $total += $item['prix'] * $item['quantite'];
        }

        $vente = Vente::create([
            'total' => $total,
            'user_id' => auth()->id()
        ]);

        foreach ($cart as $item) {
            $med = Medicament::find($item['id']);

            if ($med && $med->quantite >= $item['quantite']) {

                // 🛒 save detail
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'medicament_id' => $med->id,
                    'quantite' => $item['quantite'],
                    'prix' => $item['prix']
                ]);

                // 📦 update stock
                $med->quantite -= $item['quantite'];
                $med->save();

            } else {
                return response()->json([
                    'error' => 'Stock insuffisant pour ' . $med->nom
                ], 400);
            }
        }

        return response()->json(['message' => 'Vente enregistrée']);
    }   



}