<?php

namespace App\Http\Controllers;

use App\Models\Eligibilite;
use Illuminate\Http\Request;

class EligibiliteController extends Controller
{
    public function index()
    {
        $eligibilites = Eligibilite::with('militaire')->paginate(20);
        return view('eligibilites.index', compact('eligibilites'));
    }
}