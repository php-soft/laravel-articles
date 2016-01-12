<?php
namespace PhpSoft\Articles\Controllers;

use Input;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;

class ArticleController extends Controller
{
    private $articleModel;

    /**
     * Construct controller
     */
    public function __construct()
    {
        $this->articleModel = config('phpsoft.article.articleModel');
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
            'title'       => 'required',
            'content'     => 'required',
            'category_id' => 'required|numeric|exists:article_categories,id',
            'alias'       => 'regex:/^[a-z0-9\-]+/',
            'image'       => 'string',
            'description' => 'string',
            'order'       => 'numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.articles::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $articleModel = $this->articleModel;
        $article = $articleModel::create($request->all());

        return response()->json(arrayView('phpsoft.articles::article/read', [
            'article' => $article
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
        $articleModel = $this->articleModel;
        $article = $articleModel::find($id);

        // check exists
        if (empty($article)) {
            return response()->json(null, 404);
        }

        // validate
        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|required',
            'content'     => 'sometimes|required',
            'category_id' => 'sometimes|required|numeric|exists:article_categories,id',
            'alias'       => 'regex:/^[a-z0-9\-]+/',
            'image'       => 'string',
            'description' => 'string',
            'order'       => 'numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('phpsoft.articles::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // update
        $article = $article->update($request->all());

        // respond
        return response()->json(arrayView('phpsoft.articles::article/read', [
            'article' => $article
        ]), 200);
    }

    /**
     * move article to trash
     * @param  int $id
     * @return Response
     */
    public function moveToTrash($id)
    {
        $articleModel = $this->articleModel;
        $article = $articleModel::find($id);

        if (!$article) {
            return response()->json(null, 404);
        }

        if (!$article->delete()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $articleModel = $this->articleModel;
        $article = $articleModel::find($id);

        if (!$article) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('phpsoft.articles::article/read', [
            'article' => $article
        ]), 200);
    }

    /**
     * restore article
     * @param  int $id
     * @return Response
     */
    public function restoreFromTrash($id)
    {
        $articleModel = $this->articleModel;
        $article = $articleModel::onlyTrashed()->where('id', $id)->first();

        if (!$article) {
            return response()->json(null, 404);
        }

        if (!$article->restore()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * enable article role
     * @param  int  $id
     * @return Response
     */
    public function enable($id)
    {
        $articleModel = $this->articleModel;
        $article = $articleModel::find($id);

        if (!$article) {
            return response()->json(null, 404);
        }

        if ($article->isEnable()) {
            return response()->json(null, 204);
        }

        if (!$article->enable()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * disable article role
     * @param  int  $id
     * @return Response
     */
    public function disable($id)
    {
        $articleModel = $this->articleModel;
        $article = $articleModel::find($id);

        if (!$article) {
            return response()->json(null, 404);
        }

        if (!$article->isEnable()) {
            return response()->json(null, 204);
        }

        if (!$article->disable()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $articleModel = $this->articleModel;

        // retrieve article
        $article = $articleModel::withTrashed()->where('id', $id)->first();

        // check exists
        if (!$article) {
            return response()->json(null, 404);
        }

        $article->forceDelete();

        return response()->json(null, 204);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $isTrash = $request->is('articles/trash');
        $articleModel = $this->articleModel;

        $articles = $articleModel::browse([
            'order'     => [ Input::get('sort', 'order') => Input::get('direction', 'asc') ],
            'limit'     => ($limit = (int)Input::get('limit', 25)),
            'offset'    => (Input::get('page', 1) - 1) * $limit,
            'cursor'    => Input::get('cursor'),
            'filters'   => $request->all(),
            'trash'     => $isTrash
        ]);

        return response()->json(arrayView('phpsoft.articles::article/browse', [
            'articles' => $articles,
        ]), 200);
    }
}
