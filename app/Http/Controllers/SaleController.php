<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Http\Requests\StoreSalesRequest;
use App\Http\Requests\UpdateSalesRequest;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreSalesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sales)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sales)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSalesRequest $request, Sale $sales)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sales)
    {
        //
    }
}
