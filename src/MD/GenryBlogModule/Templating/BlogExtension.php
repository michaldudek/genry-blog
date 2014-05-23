<?php
namespace MD\GenryBlogModule\Templating;

use SplFileInfo;
use Twig_Extension;

use MD\Genry\Genry;
use MD\GenryBlogModule\Reader\Reader;

class BlogExtension extends Twig_Extension
{

    protected $reader;

    protected $genry;

    public function __construct(Reader $reader, Genry $genry) {
        $this->reader = $reader;
        $this->genry = $genry;
    }

    /**
     * Returns Twig functions registered by this extension.
     * 
     * @return array
     */
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('blog_articles', array($this, 'generateArticles'))
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

    public function generateArticles(array $requestedArticles, $template, $targetPath) {
        $articles = array();
        $targetPath = rtrim($targetPath, DS);

        foreach($requestedArticles as $slug => $sourceFile) {
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