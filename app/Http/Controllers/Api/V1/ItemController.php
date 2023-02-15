<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    const NAME = 'name';
    const CATEGORY_ID = 'category_id';
    const QUANTITY = 'quantity';
    const ACCEPTOR = 'acceptor';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $user = Auth::user();
        $items = $user->items->sortByDesc('created_at');
        $data = ItemResource::collection($items);
        $perPage = request()->input('limit', 10);
        $currentPage = request()->input('page', 1);
        $total = ceil(count($items) / $perPage);
        $currentPageItems = $data->slice(($currentPage * $perPage) - $perPage, $perPage)->values();
        //send last index for frontend pagination
        if ((int)request()->input('page', 1) >= 1) {
            $lastIndex = ($currentPage - 1) * 10 + 1;
        }

        //for keyword
        $keyword = strtolower(request()->input('keyword'));
        if ($keyword) {
            $project = Item::query()->where('name', 'Like', '%' . $keyword . '%')->get();
            $data_keyword = ItemResource::collection($project);

            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            $total = ceil(count($project) / $perPage);
            $currentPageItems = $data_keyword->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success", "data" => $currentPageItems,
                "total" => count($project), 'current_page' => $currentPage,
                'items_per_page' => $perPage, 'total_pages' => $total
            ]);
        }

        return response()->json([
            "status" => "success", "data" => $currentPageItems,
            "lastIndex" => $lastIndex,
            "total" => count($items), 'current_page' => $currentPage,
            'items_per_page' => $perPage, 'total_pages' => $total
        ]);
    }


    //get all item not paginate
    public function all_index()
    {
        $user = Auth::user();
        $items = $user->items->sortByDesc('created_at');
        $data = ItemResource::collection($items);
        return response()->json([
            "status" => "success",
            "data" => $data,
            "total" => count($items),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $category_id = trim($request->get(self::CATEGORY_ID));
            $name = trim($request->get(self::NAME));
            $item = new Item();
            $item->category_id = $category_id;
            $item->name = $name;
            $item->user_id = $user->id;
            $item->save();
            $data = new ItemResource($item);
            return success('Success', $data);
        } catch (Exception $ex) {
            return fail("Please try again!", null);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        $data = new ItemResource($item);
        return success('Success', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $category_id = trim($request->get(self::CATEGORY_ID));
        $name = trim($request->get(self::NAME));
        try {
            $item_new = Item::findOrFail($id);
            $item_new->category_id = $category_id;
            $item_new->name = $name;
            $item_new->user_id = $user->id;
            $item_new->save();
            $data = new ItemResource($item_new);

            return success('Success Created', $data);
        } catch (Exception $ex) {
            DB::rollBack();
            return fail("Please try again!", null);
        }
    }

    // update Quantity for items
    public function updateQuantity(Request $request, $id)
    {
        DB::beginTransaction();
        $user = Auth::user();
        $category_id = trim($request->get(self::CATEGORY_ID));
        $name = trim($request->get(self::NAME));
        $quantity = trim($request->get(self::QUANTITY));
        $acceptor = trim($request->get(self::ACCEPTOR));

        try {
            $item = Item::where('id', '=',  $id)->first();
            if ($item === null) {
                $item_new = new Item();
                $item_new->category_id = $category_id;
                $item_new->name = $name;
                $item_new->quantity = $quantity;
                $item_new->acceptor = $acceptor;
                $item_new->user_id = $user->id;
                $item_new->save();
                $data = new ItemResource($item_new);
                DB::commit();

                return success('Success Created', $data);
            } else {
                $item->category_id = $category_id;
                $item->name = $name;
                $item->user_id = $user->id;
                $item->acceptor = $acceptor;
                $item->quantity += $quantity;
                $item->save();
                $data = new ItemResource($item);
                DB::commit();

                return success('Success Updated', $data);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return fail("Please try again!", null);
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->delete();
            return success('Success deleted', null);
        } catch (Exception $ex) {
            return fail('Please try again!', null);
        }
    }
}
