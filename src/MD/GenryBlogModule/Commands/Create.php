<?php
namespace MD\GenryBlogModule\Commands;

use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Console\AbstractCommand;

class Create extends AbstractCommand 
{

    protected static $name = 'create';
    protected static $description = 'Creates a new blog article.';

    public function execute() {
        $title = $this->ask('Article Title', null, array(), function($answer) {
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