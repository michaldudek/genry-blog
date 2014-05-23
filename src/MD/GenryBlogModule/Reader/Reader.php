<?php
namespace MD\GenryBlogModule\Reader;

use DateTime;
use SplFileInfo;

use Michelf\MarkdownExtra;

use MD\Foundation\Exceptions\NotFoundException;

use Splot\Framework\Resources\Finder;

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
     * Constructor.
     * 
     * @param Finder $finder Splot Resource finder.
     */
    public function __construct(Finder $finder) {
        $this->finder = $finder;
    }

    /**
     * Reads an article from the given file and returns it.
     * 
     * @param SplFileInfo $file
     * @return Article
     */
    public function readFromFile(SplFileInfo $file) {
        if (!$file->isFile()) {
            throw new NotFoundException('Could not find requested blog article to read.');
        }

        $article = new Article();
        $article->setSlug($file->getFilename());

        $raw = file_get_contents($file->getPathname());

        // line 0: title
        // line 1: ======
        // line 2: date or empty
        // line 3: empty or content
        // line 4: content
        // line ...
        // line i: ########## (#x10) teaser breakpoint
        // line ...
        $lines = explode(NL, $raw);

        // set title from line 0
        $article->setTitle($lines[0]);

        $contentStartLine = 4;

        // set date from line 2, otherwise from file mtime
        $dateLine = trim($lines[2]);
        if (!empty($dateLine) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateLine)) {
            $dateElements = explode('-', $dateLine);
            $date = new DateTime('@'. mktime(12, 0, 0, $dateElements[1], $dateElements[2], $dateElements[0]));
        } else {
            $contentStartLine = 3;
            $date = new DateTime('@'. filemtime($file));
        }
        $article->setDate($date);

        // set teaser and content
        $teaser = '';
        $includeInTeaser = true;
        $content = '';

        for ($i = $contentStartLine; $i < count($lines); $i++) {
            if ($lines[$i] === '##########') {
                $includeInTeaser = false;
                continue;
            }

            if ($includeInTeaser) {
                $teaser .= NL . $lines[$i];
            }

            $content .= NL . $lines[$i];
        }

        // parse markdown for both teaser and content
        $teaser = MarkdownExtra::defaultTransform($teaser);
        $content = MarkdownExtra::defaultTransform($content);

        $article->setTeaser($teaser);
        $article->setContent($content);
        
        // set raw content
        $article->setRaw($raw);

        return $article;
    }

}