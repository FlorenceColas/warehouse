<?php

declare(strict_types=1);

namespace Common;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class PdfRendererFactory
{
    public function __invoke(ContainerInterface $container) : PdfRenderer
    {
        $config           = $container->get('config');
        $pdfAdapter       = $container->get(PdfAdapter::class);
        $templateRenderer = $container->get(TemplateRendererInterface::class);

        return new PdfRenderer($config, $pdfAdapter, $templateRenderer);
    }
}
