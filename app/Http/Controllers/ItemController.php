<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')->latest()->get();
        return view('items.index', compact('items'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        Item::create($request->all());
        return redirect('/items');
    }

    public function edit($id)
    {
        return view('items.edit', [
            'item' => Item::findOrFail($id),
            'categories' => Category::all()
        ]);
    }

    public function update(Request $request, $id)
    {
        Item::findOrFail($id)->update($request->all());
        return redirect('/items');
    }
}