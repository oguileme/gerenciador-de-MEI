<?php

namespace App\Http\Controllers;

use App\Models\Mei;
use Illuminate\Http\Request;

class MeiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(Mei::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data  = $request->validate([
            'cnpj' => 'required|string|max:14|unique:mei',
            'mei_name' => 'required|string|max:255',
            'opening_date' => 'required|date',
            'ibge_municipality_code' => 'required|string|max:7',
        ]);

        $mei = Mei::create($data);
        return response()->json($mei, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Mei $mei)
    {
        //
        if(auth()->user()->id !== $mei->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($mei);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mei $mei)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mei $mei)
    {
        //
        if(auth()->user()->id !== $mei->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $data = $request->validate([
            'mei_name' => 'sometimes|string|max:255',
            'opening_date' => 'sometimes|date',
            'ibge_municipality_code' => 'sometimes|string|max:7',
        ]);

        $mei->update($data);
        return response()->json($mei);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mei $mei)
    {
        if(auth()->user()->id !== $mei->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $mei->update(['deleted_at' => now()]);
        return response()->json(['message' => 'MEI deleted successfully']);
    }
}
