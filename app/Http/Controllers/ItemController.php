<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['category', 'parent', 'children'])
            ->orderBy('name')
            ->get();

        $parentItems = Item::orderBy('name')->get();

        return view('items.index', compact(
            'items',
            'parentItems'
        ));
    }

    public function create()
    {
        $categories = Category::all();
        $itemTypes = Item::getItemTypes();
        return view('items.create', compact('categories', 'itemTypes'));
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

    public function parentSearch(Request $request)
    {
        $search = $request->get('search', '');
        $itemId = $request->get('item_id');

        $items = Item::query()
            ->where('id', '!=', $itemId)
            ->whereNull('parent_id') // only top-level items
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name . ' (' . $item->item_code . ')',
                ];
            })
        ]);
    }

    public function updateParent(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'parent_id' => 'nullable|exists:items,id',
        ]);

        if ($request->item_id == $request->parent_id) {
            return response()->json([
                'message' => 'An item cannot be its own parent.'
            ], 422);
        }

        $item = Item::findOrFail($request->item_id);

        $item->parent_id = $request->parent_id ?: null;
        $item->save();

        return response()->json([
            'success' => true
        ]);
    }
}
