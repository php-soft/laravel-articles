<?php

$this->set('version', '1.0');
$this->set('links', $this->helper('phpsoft.articles::helpers.links', $categories['data']));
$this->set('meta', function ($section) use ($categories) {
    $section->set('offset', $categories['offset']);
    $section->set('limit', $categories['limit']);
    $section->set('total', $categories['total']);
});

$this->set('entities', $this->each($categories['data'], function ($section, $category) {

    $section->set($section->partial('phpsoft.articles::partials/category', [ 'category' => $category ]));
}));

$this->set('linked', '{}');
