<?php
namespace MD\GenryBlogModule\Reader;

use DateTime;
use SplFileInfo;

use Michelf\MarkdownExtra;

use MD\Foundation\Exceptions\NotFoundException;
use MD\Foundation\Utils\ArrayUtils;

use Splot\Framework\Resources\Finder;

use MD\Genry\Data\LoaderInterface;
use MD\GenryBlogModule\Reader\Article;

class Reader
{

    /**
     * Splot Resource Finder.
     * 
     * @var Finder
     */
    protected $finder;

    /**
     * Genry data loader.
     * 
     * @var LoaderInterface
     */
    protected $loader;

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
     * Data about all blog articles.
     * 
     * @var array
     */
    protected $data = array();

    /**
     * Articles cache.
     * 
     * @var array
     */
    private $_articles = array();

    /**
     * Has data been loaded?
     * 
     * @var boolean
     */
    private $_loaded = false;

    /**
     * Constructor.
     * 
     * @param Finder $finder Splot Resource finder.
     */
    public function __construct(
        Finder $finder,
        LoaderInterface $loader,
        $dataDir,
        $dataFile,
        $articlesDir
    ) {
        $this->finder = $finder;
        $this->loader = $loader;
        $this->dataFile = $dataFile;
        $this->articlesDir = rtrim($dataDir, DS) . DS . trim($articlesDir, DS) . DS;
    }

    /**
     * Loads information about all articles.
     * 
     * @return array
     */
    public function load() {
        if ($this->_loaded) {
            return $this->data;
        }

        try {
            $this->data = $this->loader->load($this->dataFile);
        } catch(NotFoundException $e) {
            $this->data = array();
        }

        $this->_loaded = true;
        return $this->data;
    }

    /**
     * Gets and reads blog articles.
     *
     * Returns an array filled with Article objects.
     * 
     * @param  integer $limit  [optional] How many articles to get? Default: `null`.
     * @param  integer $offset [optional] Offset from which to get the articles. Default: `0`.
     * @return array
     */
    public function getArticles($limit = null, $offset = 0) {
        $data = $this->load();
        $data = array_slice($data, $offset, $limit);

        $articles = array();
        foreach($data as $articleData) {
            $articles[] = $this->readArticle($articleData['slug']);
        }

        return $articles;
    }

    /**
     * Gets an article based on slug.
     * 
     * @param  string $slug Slug of the article to get.
     * @return Article
     */
    public function getArticle($slug) {
        $data = $this->load();
        $i = ArrayUtils::search($data, 'slug', $slug);
        if ($i === false) {
            throw new NotFoundException('Could not find blog article with slug "'. $slug .'".');
        }

        return $this->readArticle($data[$i]['slug']);
    }

    /**
     * Clears articles cache.
     */
    public function clearCache() {
        $this->_loaded = false;
        $this->_articles = array();
        $this->data = array();
    }

    /**
     * Reads an article from the given file and returns it.
     * 
     * @param SplFileInfo $file
     * @return Article
     */
    public function readFromFile(SplFileInfo $file) {
        if (!$file->isFile()) {
            throw new NotFoundException('Could not find requested blog article to read from "'. $file->getPathname() .'".');
        }

        $article = new Article();

        $filename = explode('.', $file->getFilename());
        $article->setSlug(implode('.', array_slice($filename, 0, -1)));

        $raw = file_get_contents($file->getPathname());
        $article->setRaw($raw);

        $this->readMarkdown($article, $raw);

        return $article;
    }

    /**
     * Read article based on slug or get it from cache if already there.
     * 
     * @param  string $slug Slug of the article to get.
     * @return Article
     */
    protected function readArticle($slug) {
        if (isset($this->_articles[$slug])) {
            return $this->_articles[$slug];
        }

        $file = new SplFileInfo($this->articlesDir . $slug .'.md');
        $article = $this->readFromFile($file);

        $this->_articles[$slug] = $article;
        return $article;
    }

    protected function readMarkdown(Article $article, $markdown) {
        // line 0: title
        // line 1: ======
        // line 2: date
        // line 3: ![cover](url) image
        // line 4: empty
        // line 5: teaser
        // line ...
        // line i: empty
        // line i+1: content
        // line ...

        $teaser = '';
        $inTeaser = true;
        $content = '';

        $lines = explode(NL, $markdown);
        foreach($lines as $i => $line) {
            $trimmedLine = trim($line);

            // line 0 must always be the title
            if ($i === 0) {
                $article->setTitle($trimmedLine);
                continue;
            }

            // line 1 must always be a horizontal line / hr
            if ($i === 1) {
                continue;
            }

            // line 2 must always be the date
            if ($i === 2) {
                if (!empty($trimmedLine) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $trimmedLine)) {
                    $dateElements = explode('-', $trimmedLine);
                    $date = new DateTime('@'. mktime(12, 0, 0, $dateElements[1], $dateElements[2], $dateElements[0]));
                }
                $article->setDate($date);
                continue;
            }

            // line 3 *might* be a cover image
            if ($i === 3) {
                if (!empty($trimmedLine) && preg_match('/^!\[cover\]\(([^\s]+)((\s+)(.*))?\)/sUi', $trimmedLine, $matches)) {
                    // $matches[1] is the cover URL
                    if (isset($matches[1])) {
                        $article->setCover($matches[1]);
                        continue;
                    }
                }
            }

            // if line is empty then no longer include the content in the teaser
            if (empty($trimmedLine) && $i > 4) {
                $inTeaser = false;
            }

            // include this line in either teaser or content
            if ($inTeaser) {
                $teaser .= $line . NL;
            } else {
                $content .= $line . NL;
            }
        }

        $teaser = trim($teaser);
        $content = trim($content);

        // parse markdown for both teaser and content
        $teaser = MarkdownExtra::defaultTransform($teaser);
        $content = MarkdownExtra::defaultTransform($content);

        $article->setTeaser($teaser);
        $article->setContent($content);

        return $article;

    }

}