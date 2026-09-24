<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = Item::with([
                'category:id,name',
                'parent:id,name',
            ]);

            return DataTables::eloquent($query)

                ->addColumn('category', function ($item) {
                    return $item->category?->name ?? '-';
                })

                ->addColumn('type', function ($item) {
                    return $item->item_type_name ?? '-';
                })

                ->addColumn('parent', function ($item) {

                    return view(
                        'items.parent-select',
                        compact('item')
                    )->render();
                })

                ->addColumn('action', function ($item) {

                    return '<a href="' .
                        route('items.edit', $item->id) .
                        '" class="btn btn-warning btn-xs">
                        Edit
                    </a>';
                })

                ->rawColumns([
                    'parent',
                    'action'
                ])

                ->make(true);
        }

        return view('items.index');
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
