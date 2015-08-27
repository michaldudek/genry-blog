<?php
namespace Genry\Blog\Generator;

use Genry\Genry;
use Genry\Blog\Reader\Reader;

/**
 * Generates articles.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
class Generator
{

    /**
     * Genry.
     *
     * @var Genry
     */
    protected $genry;

    /**
     * Blog reader.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Article template name.
     *
     * @var string
     */
    protected $template;

    /**
     * Article target dir.
     *
     * @var string
     */
    protected $targetDir;

    /**
     * Constructor.
     *
     * @param Genry  $genry     Genry.
     * @param Reader $reader    Blog reader.
     * @param string $template  Article template name.
     * @param string $targetDir Article target dir.
     */
    public function __construct(
        Genry $genry,
        Reader $reader,
        $template,
        $targetDir
    ) {
        $this->genry = $genry;
        $this->reader = $reader;
        $this->template = $template;
        $this->targetDir = rtrim($targetDir, DS) . DS;
    }

    /**
     * Clears all caches.
     */
    public function clearCaches()
    {
        $this->reader->clearCache();
    }

    /**
     * Queues articles to be generated.
     */
    public function queueArticlesToGenerate()
    {
        foreach ($this->reader->getArticles() as $article) {
            $this->genry->addToQueue($this->template, array(
                'article' => $article
            ), $this->targetDir . $article->getSlug() .'.html');
        }
    }
}
