<?php
namespace MD\GenryBlogModule;

use Splot\Framework\Modules\AbstractModule;

use MD\Genry\Events\WillGenerate;

use MD\GenryBlogModule\FileWatcher\BlogFilesWatcher;
use MD\GenryBlogModule\Generator\Generator;
use MD\GenryBlogModule\Reader\Reader;
use MD\GenryBlogModule\Templating\BlogExtension;
use MD\GenryBlogModule\Writer\Writer;

class GenryBlogModule extends AbstractModule
{

    protected $commandNamespace = 'blog';

    public function configure() {
        parent::configure();

        $config = $this->getConfig();
        
        // register blog reader service
        $this->container->set('blog.reader', function($c) use ($config) {
            return new Reader(
                $c->get('resource_finder'),
                $c->get('data.loader'),
                $c->getParameter('data_dir'),
                $config->get('data_file'),
                $config->get('articles_dir')
            );
        });

        $this->container->set('blog.writer', function($c) use ($config) {
            return new Writer(
                $c->get('blog.reader'),
                $c->get('data.writer'),
                $c->get('filesystem'),
                $c->getParameter('data_dir'),
                $config->get('data_file'),
                $config->get('articles_dir')
            );
        });

        $this->container->set('blog.file_watcher', function($c) use ($config) {
            return new BlogFilesWatcher(
                $c->getParameter('data_dir'),
                $config->get('data_file'),
                $config->get('articles_dir')
            );
        });

        $this->container->set('blog.twig_extension', function($c) {
            return new BlogExtension(
                $c->get('blog.reader')
            );
        });

        $this->container->set('blog.generator', function($c) use ($config) {
            return new Generator(
                $c->get('genry'),
                $c->get('blog.reader'),
                $config->get('template'),
                $config->get('target_dir')
            );
        });
    }

    public function run() {
        parent::run();

        $container = $this->container;

        if ($this->container->has('twig')) {
            $this->container->get('twig')->addExtension($this->container->get('blog.twig_extension'));
        }

        if ($this->container->has('genry')) {
            $this->container->get('genry')->addFileWatcher($this->container->get('blog.file_watcher'));
        }

        $this->container->get('event_manager')->subscribe(WillGenerate::getName(), function($event) use ($container) {
            $container->get('blog.generator')->clearCaches();
        });

        $this->container->get('event_manager')->subscribe(WillGenerate::getName(), function($event) use ($container) {
            $container->get('blog.generator')->queueArticlesToGenerate();
        });
    }

}