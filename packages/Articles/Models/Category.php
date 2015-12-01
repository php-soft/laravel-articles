<?php

namespace PhpSoft\Articles\Models;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'article_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'alias', 'image', 'description', 'parent_id', 'order', 'status'];

    /**
     * Create the model in the database.
     *
     * @param  array  $attributes
     * @return category
     */
    public static function create(array $attributes = [])
    {
        return parent::create($attributes)->fresh();
    }
}
