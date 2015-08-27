<?php
namespace Genry\Blog\FileWatcher;

use MD\Foundation\Utils\FilesystemUtils;

use Genry\FileWatcher\FileWatcherInterface;

/**
 * Tells Genry to watch article files.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
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

    /**
     * Constructor.
     *
     * @param string $dataDir     Data directory path.
     * @param string $dataFile    Name of the file where blog data is stored.
     * @param string $articlesDir Directory where all articles are stored, relative to the data dir.
     */
    public function __construct(
        $dataDir,
        $dataFile,
        $articlesDir
    ) {
        $this->dataFile = rtrim($dataDir, DS) . DS . $dataFile;
        $this->articlesDir = rtrim($dataDir, DS) . DS . trim($articlesDir, DS) . DS;
    }

    /**
     * Returns a list of files to watch.
     *
     * @return array
     */
    public function filesToWatch()
    {
        $files = FilesystemUtils::glob($this->articlesDir .'*.md');
        $files[] = $this->dataFile;
        return $files;
    }
}
