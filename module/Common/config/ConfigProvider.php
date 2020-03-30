<?php

namespace Common;

return [
    'service_manager' => [
        'factories' => [
            PdfAdapter::class               => PdfAdapterFactory::class,
            PdfRenderer::class              => PdfRendererFactory::class,
        ],
    ],
];
