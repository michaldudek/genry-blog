<?php
namespace MD\GenryBlogModule\Reader;

use MD\Foundation\MagicObject;

class Article extends MagicObject
{

    protected $__properties = array(
        'slug' => '',
        'title' => '',
        'date' => '',
        'cover' => '',
        'teaser' => '',
        'content' => '',
        'raw' => ''
    );

    public function __toString() {
        return $this->getTitle();
    }

}