<?php

namespace Warehouse\Factory;

use Common\PdfAdapter;
use Interop\Container\ContainerInterface;
use Warehouse\Controller\RecipeController;
use Zend\Db\Adapter\Adapter;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RecipeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config           = $container->get('config');
        $doctrine         = $container->get('doctrine.entitymanager.orm_default');
        $adapter          = new Adapter($config['db']);
        $templateRenderer = $container->get(TemplateRendererInterface::class);
        $pdfAdapter       = $container->get(PdfAdapter::class);

        return new RecipeController($config, $doctrine, $adapter, $templateRenderer, $pdfAdapter);
    }
}
