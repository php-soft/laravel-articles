<?php

namespace PhpSoft\Articles\Models;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;

class Article extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'articles';

    const STATUS_ENABLE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'title', 'content', 'alias', 'image', 'description', 'order', 'status'];

    /**
     * Create the model in the database.
     *
     * @param  array  $attributes
     * @return category
     */
    public static function create(array $attributes = [])
    {
        if (empty($attributes['alias'])) {
            $attributes['alias'] = Str::slug($attributes['name'])
                .'-'. Uuid::generate(4);
        }

        $attributes['user_id'] = Auth::user()->id;

        return parent::create($attributes)->fresh();
    }
}
