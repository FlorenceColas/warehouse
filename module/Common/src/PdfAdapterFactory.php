<?php

declare(strict_types=1);

namespace Common;

use Psr\Container\ContainerInterface;

class PdfAdapterFactory
{
    public function __invoke(ContainerInterface $container) : PdfAdapter
    {
        $config = $container->get('config');

        return new PdfAdapter($config);
    }
}
