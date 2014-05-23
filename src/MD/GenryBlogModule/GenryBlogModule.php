<?php
namespace MD\GenryBlogModule;

use Splot\Framework\Modules\AbstractModule;

use MD\GenryBlogModule\Reader\Reader;
use MD\GenryBlogModule\Templating\BlogExtension;

class GenryBlogModule extends AbstractModule
{

    public function configure() {
        parent::configure();
        
        // register blog reader service
        $this->container->set('blog.reader', function($c) {
            return new Reader($c->get('resource_finder'));
        });

        $this->container->set('blog.twig_extension', function($c) {
            return new BlogExtension(
                $c->get('blog.reader'),
                $c->get('genry')
            );
        });
    }

    public function run() {
        parent::run();

        if ($this->container->has('twig')) {
            $this->container->get('twig')->addExtension($this->container->get('blog.twig_extension'));
        }
    }

}