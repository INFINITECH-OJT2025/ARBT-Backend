<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {

        return response()->json(Purchase::all(), 200);
    }
}