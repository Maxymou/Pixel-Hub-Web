<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return view('permissions.index');
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        // Validation et création
        return redirect()->route('permissions.index');
    }

    public function show($id)
    {
        return view('permissions.show');
    }

    public function edit($id)
    {
        return view('permissions.edit');
    }

    public function update(Request $request, $id)
    {
        // Validation et mise à jour
        return redirect()->route('permissions.index');
    }

    public function destroy($id)
    {
        // Suppression
        return redirect()->route('permissions.index');
    }
} 