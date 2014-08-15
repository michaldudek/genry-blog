<?php
namespace MD\GenryBlogModule\Writer;

use Symfony\Component\Filesystem\Filesystem;

use MD\Foundation\Exceptions\NotUniqueException;
use MD\Foundation\Utils\ArrayUtils;
use MD\Foundation\Utils\StringUtils;

use MD\Genry\Data\WriterInterface;
use MD\GenryBlogModule\Reader\Reader;

class Writer
{

    /**
     * Blog reader.
     * 
     * @var Reader
     */
    protected $reader;

    /**
     * Data writer.
     * 
     * @var WriterInterface
     */
    protected $writer;

    /**
     * Filesystem service.
     * 
     * @var Filesystem
     */
    protected $filesystem;

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
     * @param Reader          $reader      Blog reader.
     * @param WriterInterface $writer      Genry data writer.
     * @param Filesystem      $filesystem  Filesystem service.
     * @param string          $dataDir     Data directory.
     * @param string          $dataFile    File where data is stored.
     * @param string          $articlesDir Directory where all articles are stored.
     */
    public function __construct(
        Reader $reader,
        WriterInterface $writer,
        Filesystem $filesystem,
        $dataDir,
        $dataFile,
        $articlesDir
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->filesystem = $filesystem;
        $this->dataFile = $dataFile;
        $this->articlesDir = rtrim($dataDir, DS) . DS . trim($articlesDir, DS) . DS;
    }

    /**
     * Create an article.
     *
     * Returns path to the created markdown file.
     * 
     * @param  string $title Title of the article.
     * @param  string $slug  [optional] Slug of the article. If ommitted, it will be built based on the title. Default: null.
     * @param  string $image [optional] Path to cover image, relative to the web root folder.
     * @return string
     */
    public function create($title, $slug = null, $image = null) {
        $slug = $slug ? StringUtils::urlFriendly($slug) : StringUtils::urlFriendly($name);

        $articles = $this->reader->load();

        // check if maybe there already is such article
        if (ArrayUtils::search($articles, 'slug', $slug) !== false) {
            throw new NotUniqueException('There already is a blog article with a slug "'. $slug .'".');
        }

        // create a markdown post
        $markdown = $title . NL
                    .'=========='. NL
                    . date('Y-m-d') . NL 
                    . ($image ? '![cover]('. $image .')'. NL : '')
                    . NL;
        $markdownFile = $this->articlesDir . $slug .'.md';

        // create the articles directory if doesn't exist
        $this->filesystem->dumpFile($markdownFile, $markdown);

        // add to articles array
        array_unshift($articles, array(
            'slug' => $slug,
            'title' => $title
        ));
        $this->writer->write($this->dataFile, $articles);

        return $markdownFile;
    }

}