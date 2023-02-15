<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    const NAME = 'name';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $categories = $user->categories->sortByDesc('created_at');
        $data = CategoryResource::collection($categories);
        $perPage = request()->input('limit', 10);
        $currentPage = request()->input('page', 1);
        $total = ceil(count($categories) / $perPage);
        $currentPageItems = $data->slice(($currentPage * $perPage) - $perPage, $perPage)->values();
        //send last index for frontend pagination
        if ((int)request()->input('page', 1) >= 1) {
            $lastIndex = ($currentPage - 1) * 10 + 1;
        }

        //for keyword
        $keyword = strtolower(request()->input('keyword'));
        if ($keyword) {
            $project = Category::query()->where('name', 'Like', '%' . $keyword . '%')->get();
            $data_keyword = CategoryResource::collection($project);

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
            "total" => count($categories), 'current_page' => $currentPage,
            'items_per_page' => $perPage, 'total_pages' => $total,
            // 'from' => 
        ]);

        // return response()->json(["status" => "success", "data" => CategoryResource::collection($categories), "total" => count($categories)]);
    }

    // get all category notnpaginate
    public function all_index()
    {
        $user = Auth::user();
        $categories = $user->categories->sortByDesc('created_at');
        $data = CategoryResource::collection($categories);
        return response()->json([
            "status" => "success",
            "data" => $data,
            "total" => count($categories),
        ]);
        // return response()->json(["status" => "success", "data" => CategoryResource::collection($categories), "total" => count($categories)]);
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
            $name = trim($request->get(self::NAME));
            $category = new Category();
            $category->name = $name;
            $category->user_id = $user->id;
            $category->save();

            $data = new CategoryResource($category);
            return success('Success', $data);
        } catch (Exception $ex) {
            return fail('Please try again!', null);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $data = new CategoryResource($category);
        return success('Success', $data);
        // return fail('Unauthorized, Please try again!', null);

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
    public function update(Request $request, Category $category)
    {
        try {
            $user = Auth::user();
            $name = trim($request->get(self::NAME));
            $category->name = $name;
            $category->user_id = $user->id;
            $category->save();
            $data = new CategoryResource($category);
            return success('Successfully Updated', $data);
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
    public function destroy(Category $category)
    {
        try {
            $category = Category::findOrFail($category->id);
            $category->delete();
            return success('Success deleted', null);
        } catch (Exception $ex) {
            return fail('Please try again!', null);
        }
    }
}
