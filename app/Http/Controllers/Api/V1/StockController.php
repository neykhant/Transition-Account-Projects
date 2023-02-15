<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    // const CATEGORY_ID = 'category_id';
    const SENDER = 'sender';
    const QUANTITY = 'quantity';
    const ACCEPTOR = 'acceptor';
    const ITEM_ID   = 'item_id';


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $stocks = $user->stocks->sortByDesc('created_at');
        $data = StockResource::collection($stocks);
        $perPage = request()->input('limit', 10);
        $currentPage = request()->input('page', 1);
        $total = ceil(count($stocks) / $perPage);
        $currentPageItems = $data->slice(($currentPage * $perPage) - $perPage, $perPage)->values();
        if ((int)request()->input('page', 1) >= 1) {
            $lastIndex = ($currentPage - 1) * 10 + 1;
        }
        //for keyword
        $keyword = strtolower(request()->input('keyword'));
        if ($keyword) {
            $users = DB::table('items')
                ->Join('stocks', 'items.id', '=', 'stocks.item_id')
                ->where('name', 'Like', '%' . $keyword . '%')
                ->get();

            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            $total = ceil(count($users) / $perPage);
            $currentPageItems = $users->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success", "data" => $currentPageItems,
                "total" => count($users), 'current_page' => $currentPage,
                'items_per_page' => $perPage, 'total_pages' => $total
            ]);
        }

        return response()->json(["status" => "success", "data" => $currentPageItems, 
        "lastIndex" => $lastIndex,
        "total" => count($stocks), 'current_page' => $currentPage, 
        'items_per_page' => $perPage, 'total_pages' => $total]);
    }

    //get all item not paginate
    public function all_index()
    {
        $user = Auth::user();
        $stocks = $user->stocks->sortByDesc('created_at');
        $data = StockResource::collection($stocks);
        return response()->json([
            "status" => "success",
            "data" => $data,
            "total" => count($stocks),
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
        DB::beginTransaction();
        $user = Auth::user();
        // $category_id = trim($request->get(self::CATEGORY_ID));
        $sender = trim($request->get(self::SENDER));
        $quantity = trim($request->get(self::QUANTITY));
        $acceptor = trim($request->get(self::ACCEPTOR));
        $item_id = trim($request->get(self::ITEM_ID));

        try {
            $stock = Stock::where('item_id', '=',  $item_id)->first();
            if ($stock === null) {
                $stock = new Stock();
                // $stock->category_id = $category_id;
                $stock->sender = $sender;
                $stock->item_id = $item_id;
                $stock->quantity = $quantity;
                $stock->acceptor = $acceptor;
                $stock->user_id = $user->id;
                $stock->save();
                $data = new StockResource($stock);

                DB::commit();
                return success('Successfully Created', $data);
            } else {
                // $stock->category_id = $category_id;
                $stock->sender = $sender;
                $stock->item_id = $item_id;
                $stock->quantity += $quantity;
                $stock->acceptor = $acceptor;
                $stock->user_id = $user->id;
                $stock->save();
                $data = new StockResource($stock);

                DB::commit();
                return success('Successfully Updated', $data);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return fail("Please try again!", null);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Stock $stock)
    {
        // return $stock->items->name;
        $data = new StockResource($stock);
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
        $sender = trim($request->get(self::SENDER));
        $quantity = trim($request->get(self::QUANTITY));
        $acceptor = trim($request->get(self::ACCEPTOR));
        $item_id = trim($request->get(self::ITEM_ID));

        try {
            $stock = Stock::findOrfail($id);
            $stock->item_id = $item_id;
            $stock->sender = $sender;
            $stock->quantity = $quantity;
            $stock->acceptor = $acceptor;
            $stock->user_id = $user->id;

            $stock->save();
            $data = new StockResource($stock);
            return success('Success', $data);
        } catch (Exception $ex) {
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
            $item = Stock::findOrFail($id);
            $item->delete();
            return success('Success deleted', null);
        } catch (Exception $ex) {
            return fail('Please try again!', null);
        }
    }
}
