<?php
namespace Genry\Blog\Commands;

use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Console\AbstractCommand;

/**
 * Command to create an empty blog article.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
class Create extends AbstractCommand
{

    /**
     * Command name.
     *
     * @var string
     */
    protected static $name = 'create';

    /**
     * Command description.
     *
     * @var string
     */
    protected static $description = 'Creates a new blog article.';

    /**
     * Execute the command.
     */
    public function execute()
    {
        $title = $this->ask('Article Title', null, array(), function ($answer) {
            if (empty($answer)) {
                throw new \InvalidArgumentException('The title cannot be empty');
            }
            return $answer;
        });

        $slug = $this->ask('Article Slug', StringUtils::urlFriendly($title));
        $image = $this->ask('Article Cover Image Path');

        $markdownFile = $this->get('blog.writer')->create($title, $slug, $image);
        $this->writeln('Created article <info>'. $title .'</info> in <comment>'. $markdownFile .'</comment>');
    }
}
