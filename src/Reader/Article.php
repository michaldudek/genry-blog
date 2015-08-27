<?php
namespace Genry\Blog\Reader;

use MD\Foundation\MagicObject;

/**
 * Article object.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
class Article extends MagicObject
{

    /**
     * Article properties definition.
     *
     * @var array
     */
    protected $__properties = array(
        'slug' => '',
        'title' => '',
        'date' => '',
        'cover' => '',
        'teaser' => '',
        'content' => '',
        'raw' => ''
    );

    /**
     * Converts to string by returning the title.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }
}
