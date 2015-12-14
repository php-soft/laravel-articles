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
     * Validate function
     */
    public function registerValidators()
    {
        Validator::extend('selectParent', function ($attribute, $value, $parameters,  $validator) {

            $input = $validator->getData();
            $id = $parameters[0];

            $categoryModel = $this->categoryModel;
            $parent = $categoryModel::find($input['parent_id']);

            if (!$parent) {
                return false;
            }

            return $parent->parent_id != $id;


        }, 'The selected parent id is invalid.');
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
        // validate
        $this->registerValidators();

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
            'parent_id'   => 'numeric|not_in:' . $id . ($request->parent_id == 0 || $request->parent_id == null ? '' : '|exists:article_categories,id|selectParent:' . $id),
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

    /**
     * enable category role
     * @param  int  $id
     * @return Response
     */
    public function enable($id)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::find($id);

        if (!$category) {
            return response()->json(null, 404);
        }

        if ($category->isEnable()) {
            return response()->json(null, 204);
        }

        if (!$category->enable()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * disable category role
     * @param  int  $id
     * @return Response
     */
    public function disable($id)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::find($id);

        if (!$category) {
            return response()->json(null, 404);
        }

        if (!$category->isEnable()) {
            return response()->json(null, 204);
        }

        if (!$category->disable()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * move category to trash
     * @param  int $id
     * @return Response
     */
    public function moveToTrash($id)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::find($id);

        if (!$category) {
            return response()->json(null, 404);
        }

        if (!$category->delete()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * restore category
     * @param  int $id
     * @return Response
     */
    public function restoreFromTrash($id)
    {
        $categoryModel = $this->categoryModel;
        $category = $categoryModel::onlyTrashed()->where('id', $id)->first();

        if (!$category) {
            return response()->json(null, 404);
        }

        if (!$category->restore()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $isTrash = $request->is('categories/trash');
        $categoryModel = $this->categoryModel;

        $categories = $categoryModel::browse([
            'order'     => [ Input::get('sort', 'id') => Input::get('direction', 'desc') ],
            'limit'     => ($limit = (int)Input::get('limit', 25)),
            'offset'    => (Input::get('page', 1) - 1) * $limit,
            'cursor'    => Input::get('cursor'),
            'filters'   => $request->all(),
            'trash'     => $isTrash
        ]);

        return response()->json(arrayView('phpsoft.articles::category/browse', [
            'categories' => $categories,
        ]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $categoryModel = $this->categoryModel;

        // retrieve category
        $category = $categoryModel::withTrashed()->where('id', $id)->first();

        // check exists
        if (empty($category)) {
            return response()->json(null, 404);
        }

        $category->forceDelete();

        return response()->json(null, 204);
    }
}
