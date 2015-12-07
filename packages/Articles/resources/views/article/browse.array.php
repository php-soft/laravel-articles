<?php

$this->set('version', '1.0');
$this->set('links', $this->helper('phpsoft.articles::helpers.links', $articles['data']));
$this->set('meta', function ($section) use ($articles) {
    $section->set('offset', $articles['offset']);
    $section->set('limit', $articles['limit']);
    $section->set('total', $articles['total']);
});

$this->set('entities', $this->each($articles['data'], function ($section, $article) {

    $section->set($section->partial('phpsoft.articles::partials/article', [ 'article' => $article ]));
}));

$this->set('linked', '{}');
