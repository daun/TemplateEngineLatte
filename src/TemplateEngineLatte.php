<?php

namespace TemplateEngineLatte;

use Latte\Engine;
use TemplateEngineFactory\TemplateEngineBase;

use function ProcessWire\wire;

/**
 * Provides the Latte template engine.
 */
class TemplateEngineLatte extends TemplateEngineBase
{
    const TEMP_DIR = 'TemplateEngineLatte';

    /**
     * @var Engine
     */
    protected $latte;

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = [])
    {
        // Allow overwriting view file from the controller
        if ($data['template'] ?? false) {
            $template = $data['template'];
        }

        $template = $this->normalizePath($template);
        $data = $this->getData($data);

        return $this->getLatte()->renderToString($template, $data);
    }

    /**
     * @throws \ProcessWire\WireException
     *
     * @return Engine
     */
    protected function getLatte()
    {
        if ($this->latte === null) {
            return $this->buildLatte();
        }

        return $this->latte;
    }

    /**
     * Get the Latte engine.
     *
     * @throws \ProcessWire\WireException
     * @return Engine
     */
    protected function buildLatte()
    {
        $root = $this->getTemplatesRootPath();
        $suffix = $this->moduleConfig['template_files_suffix'];

        $latte = new Engine;
        $latte->setTempDirectory(static::getLatteTempDirPath());
        $latte->setAutoRefresh((bool) $this->moduleConfig['auto_refresh']);

        if ($this->moduleConfig['simple_path_resolution']) {
            $loader = new LatteFileLoader($root, $suffix, ['dotTraversal' => true]);
        } else {
            $loader = new LatteFileLoader(null, $suffix);
        }
        $latte->setLoader($loader);

        // Set default layout file
        if ($this->moduleConfig['default_layout']) {
            $latte->addProvider('coreParentFinder', function ($template) {
                // Make sure template is not referenced (included or a layout itself)
                if (!$template->getReferenceType()) {
                    return $this->normalizePath($this->moduleConfig['default_layout']);
                }
            });
        }

        // Trigger hook to allow customization of this instance
        $this->initLatte($latte);

        $this->latte = $latte;

        return $this->latte;
    }

    /**
     * Hookable method called after Latte has been initialized.
     *
     * Use this method to customize the passed $latte instance,
     * e.g. adding functions and filters.
     *
     * @param Engine $latte
     */
    protected function ___initLatte(Engine $latte)
    {
    }

    /**
     * @param array $data
     *
     * @throws \ProcessWire\WireException
     *
     * @return array
     */
    private function getData(array $data)
    {
        // Auto-register ProcessWire API variables
        if ($this->moduleConfig['api_vars_available']) {
            foreach ($this->wire('all') as $name => $object) {
                $data[$name] = $object;
            }
        }

        return $data;
    }

    /**
     * Normalize the given template path by adding the template files suffix.
     *
     * @param string $template
     *
     * @return string
     */
    private function normalizePath($path)
    {
        $suffix = $this->moduleConfig['template_files_suffix'];

        $path = ltrim($path, DIRECTORY_SEPARATOR);

        if (!preg_match("/\.${suffix}$/", $path)) {
            $path = "{$path}.{$suffix}";
        }

        // Prepend root path
        if (!$this->moduleConfig['simple_path_resolution']) {
            return $this->getTemplatesRootPath() . $path;
        } else {
            return $path;
        }
    }

    /**
     * Get the path to Latte's temp directory.
     *
     */
    public static function getLatteTempDirPath(): string
    {
        return wire()->config->paths->cache . self::TEMP_DIR;
    }

    /**
     * Clear Latte's temp directory.
     *
     */
    public static function clearLatteTempDir()
    {
        $temp = static::getLatteTempDirPath();
        if (file_exists($temp)) {
            $files = glob($temp . '/*');
            array_map('unlink', $files);
            return count($files);
        } else {
            return false;
        }
    }
}
