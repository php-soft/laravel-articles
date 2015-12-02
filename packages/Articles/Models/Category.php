<?php

namespace PhpSoft\Articles\Models;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;

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
        if (empty($attributes['alias'])) {
            $attributes['alias'] = Str::slug($attributes['name'])
                .'-'. Uuid::generate(4);
        }

        return parent::create($attributes)->fresh();
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @return bool|int
     */
    public function update(array $attributes = [])
    {
        if (isset($attributes['alias']) && empty($attributes['alias'])) {
            $name = $this->name;

            if (isset($attributes['name'])) {
                $name = $attributes['name'];
            }

            $attributes['alias'] = Str::slug($name)
                .'-'.Uuid::generate(4);
        }

        if (!parent::update($attributes)) {
            throw new Exception('Cannot update category.');
        }

        return $this;
    }
}
