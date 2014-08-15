<?php
namespace MD\GenryBlogModule\FileWatcher;

use MD\Foundation\Utils\FilesystemUtils;

use MD\Genry\FileWatcher\FileWatcherInterface;

class BlogFilesWatcher implements FileWatcherInterface
{

    /**
     * Name of the file where blog data is stored.
     * 
     * @var string
     */
    protected $dataFile;

    /**
     * Directory where all articles are stored, relative to the data dir.
     * 
     * @var string
     */
    protected $articlesDir;

    public function __construct(
        $dataDir,
        $dataFile,
        $articlesDir
    ) {
        $this->dataFile = rtrim($dataDir, DS) . DS . $dataFile;
        $this->articlesDir = rtrim($dataDir, DS) . DS . trim($articlesDir, DS) . DS;
    }

    public function filesToWatch() {
        $files = FilesystemUtils::glob($this->articlesDir .'*.md');
        $files[] = $this->dataFile;
        return $files;
    }

}