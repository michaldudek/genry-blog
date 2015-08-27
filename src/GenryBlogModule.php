<?php
namespace Genry\Blog;

use Splot\Framework\Modules\AbstractModule;

/**
 * Blog module for Genry.
 *
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 */
class GenryBlogModule extends AbstractModule
{

    /**
     * Namespace for all commands in this module.
     *
     * @var string
     */
    protected $commandNamespace = 'blog';

    /**
     * Configures the module.
     */
    public function configure()
    {
        parent::configure();

        $config = $this->getConfig();
        $this->container->setParameter('blog.data_file', $config->get('data_file'));
        $this->container->setParameter('blog.articles_dir', $config->get('articles_dir'));
        $this->container->setParameter('blog.template', $config->get('template'));
        $this->container->setParameter('blog.target_dir', $config->get('target_dir'));
    }
}
