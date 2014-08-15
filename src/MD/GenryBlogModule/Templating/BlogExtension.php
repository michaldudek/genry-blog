<?php
namespace MD\GenryBlogModule\Templating;

use SplFileInfo;
use Twig_Extension;

use MD\GenryBlogModule\Reader\Reader;

class BlogExtension extends Twig_Extension
{

    protected $reader;

    public function __construct(Reader $reader) {
        $this->reader = $reader;
    }

    /**
     * Returns Twig functions registered by this extension.
     * 
     * @return array
     */
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('blog_articles', array($this, 'getArticles')),
            new \Twig_SimpleFunction('blog_article', array($this, 'getArticle'))
        );
    }

    /**
     * Returns the name of this extension.
     * 
     * @return string
     */
    public function getName() {
        return 'genry_blog_module.extension';
    }

    public function getArticles($limit = null, $offset = 0) {
        return $this->reader->getArticles($limit, $offset);
    }

    public function getArticle($slug) {
        return $this->reader->getArticle($slug);
    }

}