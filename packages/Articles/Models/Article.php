<?php

namespace PhpSoft\Articles\Models;

use Auth;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;

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
    protected $fillable = ['user_id', 'category_id', 'title', 'content', 'alias', 'image', 'description', 'order'];

    /**
     * Make relationship to category.
     *
     * @return relationship
     */
    public function category()
    {
        return $this->belongsTo('PhpSoft\Articles\Models\Category', 'category_id'); // @codeCoverageIgnore
    }

    /**
     * relation to table users
     * @return relation
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id'); // @codeCoverageIgnore
    }

    /**
     * Create the model in the database.
     *
     * @param  array  $attributes
     * @return category
     */
    public static function create(array $attributes = [])
    {
        if (empty($attributes['alias'])) {
            $attributes['alias'] = Str::slug($attributes['title']);
        }

        $attributes['user_id'] = Auth::user()->id;

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
            $title = $this->title;

            if (isset($attributes['title'])) {
                $title = $attributes['title'];
            }

            $attributes['alias'] = Str::slug($title);
        }

        if (!parent::update($attributes)) {
            throw new Exception('Cannot update article.');
        }

        return $this;
    }

    /**
     * set status enable
     * @return boolean
     */
    public function enable()
    {
        $this->status = $this->status | Article::STATUS_ENABLE;
        return $this->save();
    }

    /**
     * set status disable
     * @return boolean
     */
    public function disable()
    {
        $this->status = $this->status & ~Article::STATUS_ENABLE;
        return $this->save();
    }

    /**
     * check status enable
     * @return boolean [description]
     */
    public function isEnable()
    {
        return Article::STATUS_ENABLE == ($this->status & Article::STATUS_ENABLE);
    }

    /**
     * Browse items
     *
     * @param  array  $options
     * @return array
     */
    public static function browse($options = [])
    {
        $articleModel = config('phpsoft.article.articleModel');

        $find = new $articleModel;
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
