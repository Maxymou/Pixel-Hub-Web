<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return view('roles.index');
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        // Validation et création
        return redirect()->route('roles.index');
    }

    public function show($id)
    {
        return view('roles.show');
    }

    public function edit($id)
    {
        return view('roles.edit');
    }

    public function update(Request $request, $id)
    {
        // Validation et mise à jour
        return redirect()->route('roles.index');
    }

    public function destroy($id)
    {
        // Suppression
        return redirect()->route('roles.index');
    }
} 