<?php

namespace App\Http\Controllers;

use App\Http\Resources\DamageItemResource;
use App\Http\Resources\InStockReportResource;
use App\Http\Resources\OutStockResource;
use App\Models\DamageItem;
use App\Models\OutStock;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function getInStockReport()
    {

        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');

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

        if (!is_null($startDate)  && !is_null($endDate)) {

            $Stocks = Stock::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->orderBy('created_at', 'desc')->get();
            $totalStockAll = $Stocks->sum('quantity');


            $totalStock = InStockReportResource::collection($Stocks);
            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            //send last index for frontend pagination
            if ((int)request()->input('page', 1) >= 1) {
                $lastIndex = ($currentPage - 1) * 10 + 1;
            }

            $total = ceil(count($totalStock) / $perPage);
            $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success",
                "lastIndex" => $lastIndex,
                "data" => $currentPageItems, "total" => count($totalStock),
                'current_page' => $currentPage, 'items_per_page' => $perPage,
                'total_pages' => $total, 'totalInStockQuantity' => $totalStockAll
            ]);
        } else {
            $totalStock = Stock::select('*')
                ->whereMonth('created_at', Carbon::now()->month)
                ->orderBy('created_at', 'desc')
                ->get();

            $totalStockAll = $totalStock->sum('quantity');
            $totalStock = InStockReportResource::collection($totalStock);

            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            //send last index for frontend pagination
            if ((int)request()->input('page', 1) >= 1) {
                $lastIndex = ($currentPage - 1) * 10 + 1;
            }
            $total = ceil(count($totalStock) / $perPage);
            $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success",
                "lastIndex" => $lastIndex,
                "data" => $currentPageItems, "total" => count($totalStock),
                'current_page' => $currentPage, 'items_per_page' => $perPage,
                'total_pages' => $total, 'totalInStockQuantity' => $totalStockAll
            ]);
        }
    }

    public function getOutStockReport()
    {
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');

        $keyword = strtolower(request()->input('keyword'));
        if ($keyword) {
            $users = DB::table('items')
                ->Join('stocks', 'items.id', '=', 'stocks.item_id')
                ->Join('out_stocks', 'stocks.id', '=', 'out_stocks.stock_id')
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

        if (!is_null($startDate)  && !is_null($endDate)) {

            $Stocks = OutStock::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->orderBy('created_at', 'desc')->get();
            $totalStockAll = $Stocks->sum('quantity');

            $totalStock = OutStockResource::collection($Stocks);
            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            //send last index for frontend pagination
            if ((int)request()->input('page', 1) >= 1) {
                $lastIndex = ($currentPage - 1) * 10 + 1;
            }
            $total = ceil(count($totalStock) / $perPage);
            $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success",
                "lastIndex" => $lastIndex,

                "data" => $currentPageItems, "total" => count($totalStock),
                'current_page' => $currentPage, 'items_per_page' => $perPage,
                'total_pages' => $total, 'totalInStockQuantity' => $totalStockAll
            ]);
        } else {
            $totalStock = OutStock::select('*')
                ->whereMonth('created_at', Carbon::now()->month)
                ->orderBy('created_at', 'desc')
                ->get();

            $totalStockAll = $totalStock->sum('quantity');
            $totalStock = OutStockResource::collection($totalStock);

            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            //send last index for frontend pagination
            if ((int)request()->input('page', 1) >= 1) {
                $lastIndex = ($currentPage - 1) * 10 + 1;
            }
            $total = ceil(count($totalStock) / $perPage);
            $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success",
                "lastIndex" => $lastIndex,
                "data" => $currentPageItems, "total" => count($totalStock),
                'current_page' => $currentPage, 'items_per_page' => $perPage,
                'total_pages' => $total, 'totalInStockQuantity' => $totalStockAll
            ]);
        }
    }

    public function getDamageReport()
    {
        // $totalDamageItems = new DamageItem();
        // if (request()->start_date && request()->end_date) {
        //     $totalDamageItems = $totalDamageItems->whereBetween('created_at', [request()->start_date . ' 00:00:00', request()->end_date . ' 23:59:59']);
        //     $totalDamageItems = $totalDamageItems->get();
        //     $totalDamageItemsAll = $totalDamageItems->sum('quantity');

        //     $totalStock = DamageItemResource::collection($totalDamageItems);
        //     $perPage = request()->input('limit', 10);
        //     $currentPage = request()->input('page', 1);
        //     $total = ceil(count($totalStock) / $perPage);
        //     $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

        //     return response()->json([
        //         "status" => "success",
        //         "data" => $currentPageItems, "total" => count($totalStock),
        //         'current_page' => $currentPage, 'items_per_page' => $perPage,
        //         'total_pages' => $total, 'totalInStockQuantity' => $totalDamageItemsAll
        //     ]);
        // } else {

        //     $totalDamageItems = $totalDamageItems->get();
        //     $totalDamageItemsAll = $totalDamageItems->sum('quantity');

        //     $perPage = request()->input('limit', 10);
        //     $currentPage = request()->input('page', 1);
        //     $total = ceil(count($totalDamageItems) / $perPage);
        //     $currentPageItems = $totalDamageItems->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

        //     return response()->json([
        //         "status" => "success",
        //         "data" => $currentPageItems, "total" => count($totalDamageItems),
        //         'current_page' => $currentPage, 'items_per_page' => $perPage,
        //         'total_pages' => $total, 'totalDamageQuantity' => $totalDamageItemsAll
        //     ]);
        // }

        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');

        $keyword = strtolower(request()->input('keyword'));
        if ($keyword) {
            $users = DB::table('items')
                ->Join('stocks', 'items.id', '=', 'stocks.item_id')
                ->Join('damage_items', 'stocks.id', '=', 'damage_items.stock_id')
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

        if (!is_null($startDate)  && !is_null($endDate)) {

            $Stocks = DamageItem::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])->orderBy('created_at', 'desc')->get();
            $totalStockAll = $Stocks->sum('quantity');

            $totalStock = DamageItemResource::collection($Stocks);
            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            //send last index for frontend pagination
            if ((int)request()->input('page', 1) >= 1) {
                $lastIndex = ($currentPage - 1) * 10 + 1;
            }
            $total = ceil(count($totalStock) / $perPage);
            $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success",
                "lastIndex" => $lastIndex,
                "data" => $currentPageItems, "total" => count($totalStock),
                'current_page' => $currentPage, 'items_per_page' => $perPage,
                'total_pages' => $total, 'totalInStockQuantity' => $totalStockAll
            ]);
        } else {
            $totalStock = DamageItem::select('*')
                ->whereMonth('created_at', Carbon::now()->month)
                ->orderBy('created_at', 'desc')
                ->get();

            $totalStockAll = $totalStock->sum('quantity');
            $totalStock = DamageItemResource::collection($totalStock);

            $perPage = request()->input('limit', 10);
            $currentPage = request()->input('page', 1);
            //send last index for frontend pagination
            if ((int)request()->input('page', 1) >= 1) {
                $lastIndex = ($currentPage - 1) * 10 + 1;
            }
            $total = ceil(count($totalStock) / $perPage);
            $currentPageItems = $totalStock->slice(($currentPage * $perPage) - $perPage, $perPage)->values();

            return response()->json([
                "status" => "success",
                "lastIndex" => $lastIndex,
                "data" => $currentPageItems, "total" => count($totalStock),
                'current_page' => $currentPage, 'items_per_page' => $perPage,
                'total_pages' => $total, 'totalInStockQuantity' => $totalStockAll
            ]);
        }
    }
}
