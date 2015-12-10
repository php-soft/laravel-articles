<?php

namespace PhpSoft\Articles\Models;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;

class Category extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'article_categories';

    const STATUS_ENABLE = 1;

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

    /**
     * Find by id or alias
     *
     * @param  string $idOrAlias
     * @return Category
     */
    public static function findByIdOrAlias($idOrAlias)
    {
        $category = parent::find($idOrAlias);

        if ($category) {
            return $category;
        }

        return parent::where('alias', $idOrAlias)->first();
    }

    /**
     * set status enable
     * @return boolean
     */
    public function enable()
    {
        $this->status = $this->status | Category::STATUS_ENABLE;
        return $this->save();
    }

    /**
     * set status disable
     * @return boolean
     */
    public function disable()
    {
        $this->status = $this->status & ~Category::STATUS_ENABLE;
        return $this->save();
    }

    /**
     * check status enable
     * @return boolean [description]
     */
    public function isEnable()
    {
        return Category::STATUS_ENABLE == ($this->status & Category::STATUS_ENABLE);
    }

    /**
     * Browse items
     *
     * @param  array  $options
     * @return array
     */
    public static function browse($options = [])
    {
        $categoryModel = config('phpsoft.article.categoryModel');

        $find = new $categoryModel;
        $fillable = $find->fillable;

        if ($options['trash']) {
            $find = $find->onlyTrashed();
        }

        if (!empty($options['filters'])) {
            $inFilters = array_intersect($fillable, array_keys($options['filters']));

            foreach ($inFilters as $key) {
                $find = ($options['filters'][$key] == null) ? $find : $find->where($key, 'LIKE', $options['filters'][$key]);
            }
        }

        if (!empty($options['order'])) {
            foreach ($options['order'] as $field => $direction) {
                if (in_array($field, $fillable)) {
                    $find = $find->orderBy($field, $direction);
                }
            }

            $find = $find->orderBy('id', 'DESC');
        }

        $total = $find->count();

        if (!empty($options['offset'])) {
            $find = $find->skip($options['offset']);
        }

        if (!empty($options['limit'])) {
            $find = $find->take($options['limit']);
        }

        if (!empty($options['cursor'])) {
            $find = $find->where('id', '<', $options['cursor']);
        }

        return [
            'total'  => $total,
            'offset' => empty($options['offset']) ? 0 : $options['offset'],
            'limit'  => empty($options['limit']) ? 0 : $options['limit'],
            'data'   => $find->get(),
        ];
    }
}
