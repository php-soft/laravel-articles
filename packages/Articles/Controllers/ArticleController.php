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
            'alias'       => 'regex:/^[a-z0-9\-]+/|unique:articles',
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
}
