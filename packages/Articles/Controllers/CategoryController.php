<?php
namespace PhpSoft\Articles\Controllers;

use Input;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class CategoryController extends Controller
{
    private $categoryModel;

    /**
     * Construct controller
     */
    public function __construct()
    {
        $this->categoryModel = config('phpsoft.article.categoryModel');
    }

    /**
     * Create resource action
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required',
            'alias'       => 'regex:/^[a-z0-9\-]+/|unique:article_categories',
            'image'       => 'string',
            'description' => 'string',
            'parent_id'   => 'numeric' . ($request->parent_id == 0 || $request->parent_id == null ? '' : '|exists:article_categories,id'),
            'order'       => 'numeric',
            'status'      => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.articles::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $categoryModel = $this->categoryModel;
        $category = $categoryModel::create($request->all());

        return response()->json(arrayView('phpsoft.articles::category/read', [
            'category' => $category
        ]), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int     $id
     * @param  Request $request
     * @return Response
     */
    public function update($id, Request $request)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::find($id);

        // check exists
        if (empty($category)) {
            return response()->json(null, 404);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'name'        => 'sometimes|required|string',
            'alias'       => 'regex:/^[a-z0-9\-]+/|unique:article_categories,alias,' . $category->id,
            'image'       => 'string',
            'description' => 'string',
            'parent_id'   => 'numeric|not_in:' . $id . ($request->parent_id == 0 || $request->parent_id == null ? '' : '|exists:article_categories,id'),
            'order'       => 'numeric',
            'status'      => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.articles::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // update
        $category = $category->update($request->all());

        // respond
        return response()->json(arrayView('phpsoft.articles::category/read', [
            'category' => $category
        ]), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idOrAlias
     * @return Response
     */
    public function show($idOrAlias)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::findByIdOrAlias($idOrAlias);

        if (empty($category)) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('phpsoft.articles::category/read', [
            'category' => $category
        ]), 200);
    }
}
