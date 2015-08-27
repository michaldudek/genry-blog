<?php
namespace Genry\Blog\Templating;

use SplFileInfo;
use Twig_Extension;

use Genry\Blog\Reader\Article;
use Genry\Blog\Reader\Reader;

/**
 * Blog extension for Twig.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
class BlogExtension extends Twig_Extension
{

    /**
     * Blog Reader.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Constructor.
     *
     * @param Reader $reader Blog reader.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Returns Twig functions registered by this extension.
     *
     * @return array
     */
    public function getFunctions()
    {
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
    public function getName()
    {
        return 'genry_blog_module.extension';
    }

    /**
     * Returns a list of blog articles.
     *
     * @param  integer $limit  Limit.
     * @param  integer $offset Start offset.
     *
     * @return array
     */
    public function getArticles($limit = null, $offset = 0)
    {
        return $this->reader->getArticles($limit, $offset);
    }

    /**
     * Returns an article based on a slug.
     *
     * @param  string $slug Article slug.
     *
     * @return Article
     */
    public function getArticle($slug)
    {
        return $this->reader->getArticle($slug);
    }
}
