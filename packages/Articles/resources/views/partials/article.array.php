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
