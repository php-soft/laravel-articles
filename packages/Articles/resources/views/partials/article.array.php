<?php

$this->extract($article, [
    'id',
    'title',
    'content',
    'alias',
    'image',
    'description',
    'order',
    'status',
]);

$this->set('user', ['id' => $article->user_id]);

$this->set('createdAt', date('c', strtotime($article->created_at)));

$this->set('category', function ($section) use ($article) {

    $section->set($section->partial('phpsoft.articles::partials/category', [ 'category' => $article->category ]));
});

$this->set('isEnable', $article->isEnable());
