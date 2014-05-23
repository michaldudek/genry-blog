<?php
namespace MD\GenryBlogModule\Reader;

use MD\Foundation\MDObject;

class Article extends MDObject
{

    protected $__properties = array(
        'slug' => '',
        'title' => '',
        'date' => '',
        'teaser' => '',
        'content' => '',
        'raw' => ''
    );

    public function __toString() {
        return $this->getTitle();
    }

}