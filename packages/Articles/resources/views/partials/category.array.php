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

    $parent = $category->parent;
    $section->set(($parent == null) ? null : $section->partial(
        'phpsoft.articles::partials/category', [ 'category' => $parent ]
    ));
});

$this->set('createdAt', date('c', strtotime($category->created_at)));

$this->set('isEnable', $category->isEnable());

if ($category->trashed()) {
    $this->set('deletedAt', date('c', strtotime($category->deleted_at)));
}
