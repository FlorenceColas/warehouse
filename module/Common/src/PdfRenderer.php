<?php

declare(strict_types=1);

namespace Common;

use Zend\Expressive\Template\TemplateRendererInterface;

/**
 * Class PdfRenderer
 *
 * @package AAA\Common
 */
class PdfRenderer
    implements TemplateRendererInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var PdfAdapter
     */
    protected $pdfAdapter;

    /**
     * @var TemplateRendererInterface
     */
    protected $templateRenderer;

    /**
     * @param array                     $config
     * @param PdfAdapter                $pdfAdapter
     * @param TemplateRendererInterface $templateRenderer
     */
    public function __construct(
        array $config,
        PdfAdapter $pdfAdapter,
        TemplateRendererInterface $templateRenderer
    ) {
        $this->config           = $config;
        $this->pdfAdapter       = $pdfAdapter;
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return string
     */
    public function render(string $name, $params = []): string
    {
        $content = $this->templateRenderer->render($name, $params);

        $pdf = $this->pdfAdapter
             ->setContent($content, $this->config['dir']['tmp'])
             ->render();

        return $pdf;
    }

    /**
     * @param string      $path
     * @param string|null $namespace
     */
    public function addPath(string $path, string $namespace = null): void
    {
        $this->templateRenderer->addPath($path, $namespace);
    }

    /**
     * @return array
     */
    public function getPaths(): array
    {
        return $this->templateRenderer->getPaths();
    }

    /**
     * @param string $templateName
     * @param string $param
     * @param mixed  $value
     */
    public function addDefaultParam(string $templateName, string $param, $value): void {
        $this->templateRenderer->addDefaultParam($templateName, $param, $value);
    }
}
