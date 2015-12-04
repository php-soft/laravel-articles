<?php

$this->set('version', '1.0');
$this->set('links', '{}');
$this->set('meta', '{}');

$this->set('entities', $this->each([ $article ], function ($section, $article) {

    $section->set($section->partial('phpsoft.articles::partials/article', [ 'article' => $article ]));
}));

$this->set('linked', '{}');
