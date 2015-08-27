<?php
namespace Genry\BlogModule\Templating;

use SplFileInfo;
use Twig_Extension;

use Genry\Genry;
use Genry\BlogModule\Reader\Reader;

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
     * Genry.
     *
     * @var Genry
     */
    protected $genry;

    /**
     * Constructor.
     *
     * @param Reader $reader Blog reader.
     * @param Genry  $genry  Genry
     */
    public function __construct(Reader $reader, Genry $genry)
    {
        $this->reader = $reader;
        $this->genry = $genry;
    }

    /**
     * Returns Twig functions registered by this extension.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('blog_articles', array($this, 'generateArticles'))
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
     * Generates articles.
     *
     * @param  array  $requestedArticles List of articles to generate.
     * @param  string $template          Template name for articles.
     * @param  string $targetPath        Path where the articles should be saved.
     *
     * @return array
     */
    public function generateArticles(array $requestedArticles, $template, $targetPath)
    {
        $articles = array();
        $targetPath = rtrim($targetPath, DS);

        foreach ($requestedArticles as $slug => $sourceFile) {
            $article = $this->reader->readFromFile(new SplFileInfo($sourceFile));
            $article->setSlug($slug);
            $articles[] = $article;

            // also generate full page for this article (but delay it by adding to the queue
            // in order to not mess up assets containers)
            $this->genry->addToQueue($template, array(
                'article' => $article
            ), $targetPath . DS . $slug .'.html');
        }

        return $articles;
    }
}
