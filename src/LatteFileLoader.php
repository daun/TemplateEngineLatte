<?php

namespace TemplateEngineLatte;

use Latte\Loaders\FileLoader;

/**
 * Custom Latte file loader.
 *
 * Enables a base directory prepended to all paths.
 *
 * Adds optional dot syntax for directory traversal:
 *   partials.home.slider >> /path/to/view/dir/partials/home/slider.latte
 *
 */
class LatteFileLoader extends FileLoader
{
    /** @var string */
    protected $suffix;

    /** @var array */
    protected $options;

    public function __construct($baseDir = null, $suffix = 'latte', $options = [])
    {
        $this->baseDir = $baseDir
            ? $this->normalizePath(rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
            : null;
        $this->suffix = ltrim($suffix, '.');
        $this->options = $options + [
            'dotTraversal' => false,
        ];
    }

    /**
     * Returns template source code.
     *
     */
    public function getContent($fileName): string
    {
        // Remove base dir from beginning
        if ($this->baseDir) {
            $fileName = $this->leftTrim($fileName, $this->baseDir);
        }

        // Allow dot syntax for paths resolution
        if ($this->options['dotTraversal']) {
            $fileName = $this->rightTrim($fileName, ".{$this->suffix}");
            $fileName = str_replace('.', '/', $fileName);
            $fileName = "{$fileName}.{$this->suffix}";
        }

        return parent::getContent($fileName);
    }

    /**
     * Returns referred template name.
     *
     */
    public function getReferredName($file, $referringFile): string
    {
        return $file;
    }

    /**
     * Remove string from start of string.
     *
     */
    private function leftTrim($str, $needle)
    {
        if (stripos($str, $needle) === 0) {
            $str = substr($str, strlen($needle));
        }
        return $str;
    }

    /**
     * Remove string from end of string.
     *
     */
    private function rightTrim($str, $needle)
    {
        if (stripos($str, $needle, strlen($str) - strlen($needle)) !== false) {
            $str = substr($str, 0, -strlen($needle));
        }
        return $str;
    }
}
