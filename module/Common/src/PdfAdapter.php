<?php

declare(strict_types=1);

namespace Common;

class PdfAdapter
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $tmpHtmlFile;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return self
     */
    public function cleanup(): self
    {
        if (
            is_file($this->tmpHtmlFile)
            and is_writable($this->tmpHtmlFile)
        ) {
            unlink($this->tmpHtmlFile);
        }

        unset($this->tmpHtmlFile);

        return $this;
    }

    /**
     * @param $args
     *
     * @return string
     */
    public function render(string $args = null): string
    {
        if (empty($this->config['path']['wkhtmltopdf'])) {
            throw new \RuntimeException('Wkhtmltopdf path is not set in config');
        }

        $path = $this->config['path']['wkhtmltopdf'];

        if (! is_executable($path)) {
            throw new \RuntimeException("$path is not executable.");
        }

        if (! is_file($this->tmpHtmlFile)) {
            throw new \RuntimeException("{$this->tmpHtmlFile} is not a file.");
        }

        $cmd = sprintf('%1$s %2$s page %3$s -', $path, $args, $this->tmpHtmlFile);

        $proc = proc_open(
            $cmd,
            array(
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            ),
            $pipes
        );

        $pdf = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $ret = proc_close($proc);

        $this->cleanup();

        if (0 != $ret) {
            throw new \RuntimeException("Error during rendering : $ret : $stderr");
        }

        return $pdf;
    }

    /**
     * @param string $html
     * @param string $tmpPath
     *
     * @return self
     */
    public function setContent($html, $tmpPath = null): self
    {
        is_dir((string) $tmpPath) or $tmpPath = sys_get_temp_dir();

        $tmpHtmlFile = tempnam($tmpPath, 'wkhtmltopdf_');

        // wkhtmltopdf *NEEDS* the file to have .htm extension
        rename($tmpHtmlFile, $tmpHtmlFile .= '.htm');

        file_put_contents($tmpHtmlFile, $html);

        $this->tmpHtmlFile = $tmpHtmlFile;

        return $this;
    }
}
