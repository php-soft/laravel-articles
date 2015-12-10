<?php

$this->extract($category, [
    'id',
    'name',
    'alias',
    'image',
    'description',
    'order',
    'status',
]);

$this->set('parent', function ($section) use ($category) {

    $category = PhpSoft\Articles\Models\Category::find($category->parent_id);
    $section->set(($category == null) ? null : $section->partial(
        'phpsoft.articles::partials/category', [ 'category' => $category ]
    ));
});

$this->set('createdAt', date('c', strtotime($category->created_at)));

$this->set('isEnable', $category->isEnable());

if ($category->trashed()) {
    $this->set('deletedAt', date('c', strtotime($category->deleted_at)));
}
