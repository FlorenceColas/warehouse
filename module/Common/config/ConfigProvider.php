<?php

namespace Common;

return [
    'service_manager' => [
        'factories' => [
//            Helper\Translate::class         => Helper\TranslateFactory::class,
//            Helper\TranslatePlural::class   => Helper\TranslatePluralFactory::class,
            PdfAdapter::class               => PdfAdapterFactory::class,
            PdfRenderer::class              => PdfRendererFactory::class,
        ],
        'invokables' => [
//            Helper\DateFormat::class        => Helper\DateFormat::class,
//            Helper\NumberFormat::class      => Helper\NumberFormat::class,
        ],
    ],
];
