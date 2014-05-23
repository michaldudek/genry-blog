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
        $article->setRaw($raw);

        $this->readMarkdown($article, $raw);

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