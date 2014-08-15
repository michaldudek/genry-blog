<?php
namespace MD\GenryBlogModule\Generator;

use MD\Genry\Genry;
use MD\GenryBlogModule\Reader\Reader;

class Generator
{

    protected $genry;

    protected $reader;

    protected $template;

    protected $targetDir;

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

    public function clearCaches() {
        $this->reader->clearCache();
    }

    public function queueArticlesToGenerate() {
        foreach($this->reader->getArticles() as $article) {
            $this->genry->addToQueue($this->template, array(
                'article' => $article
            ), $this->targetDir . $article->getSlug() .'.html');
        }
    }

}