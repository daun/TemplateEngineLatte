<?php

namespace ProcessWire;

use TemplateEngineLatte\TemplateEngineLatte as LatteEngine;

/**
 * Adds Latte templates to the TemplateEngineFactory module.
 */
class TemplateEngineLatte extends WireData implements Module, ConfigurableModule
{
    /**
     * @var array
     */
    private static $defaultConfig = [
        'template_files_suffix' => 'latte',
        'default_layout' => '',
        'api_vars_available' => 1,
        'simple_path_resolution' => 0,
        'auto_refresh' => 1,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->wire('classLoader')->addNamespace('TemplateEngineLatte', __DIR__ . '/src');
        $this->setDefaultConfig();
    }

    /**
     * @return array
     */
    public static function getModuleInfo()
    {
        return [
            'title' => 'Template Engine Latte',
            'summary' => 'Latte templates for the TemplateEngineFactory',
            'version' => 200,
            'author' => 'Philipp Daun',
            'href' => 'https://github.com/daun/TemplateEngineLatte',
            'singular' => true,
            'autoload' => true,
            'requires' => [
                'TemplateEngineFactory>=2.0.0',
                'PHP>=8.0',
                'ProcessWire>=3.0',
            ],
        ];
    }

    public function init()
    {
        /** @var \ProcessWire\TemplateEngineFactory $factory */
        $factory = $this->wire('modules')->get('TemplateEngineFactory');

        $factory->registerEngine('Latte', new LatteEngine($factory->getArray(), $this->getArray()));
    }

    private function setDefaultConfig()
    {
        foreach (self::$defaultConfig as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param array $data
     *
     * @throws \ProcessWire\WireException
     * @throws \ProcessWire\WirePermissionException
     *
     * @return \ProcessWire\InputfieldWrapper
     */
    public static function getModuleConfigInputfields(array $data)
    {
        /** Clear Latte temp dirctory? */
        $post = wire('input')->post;
        if ($post->submit_save_module && $post->clear_temp) {
            if (LatteEngine::clearLatteTempDir()) {
                wire()->message(__CLASS__ . ': ' . __(' Latte cache cleared.', __FILE__));
            }
        }

        /** @var Modules $modules */
        $data = array_merge(self::$defaultConfig, $data);
        $wrapper = new InputfieldWrapper();
        $modules = wire('modules');

        /** @var \ProcessWire\InputfieldText $field */
        $field = $modules->get('InputfieldText');
        $field->label = __('Template files suffix');
        $field->name = 'template_files_suffix';
        $field->value = $data['template_files_suffix'];
        $field->required = 1;
        $wrapper->append($field);

        /** @var \ProcessWire\InputfieldText $field */
        $field = $modules->get('InputfieldText');
        $field->label = __('Default layout');
        $field->description = __('The base layout file that views will use, relative to the views directory.');
        $field->note = __('Example: `layouts/@default.latte` will set `site/templates/views/layouts/@default.latte`');
        $field->name = 'default_layout';
        $field->value = $data['default_layout'];
        $wrapper->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Provide ProcessWire API variables in Latte templates');
        $field->description = __('API variables (`$pages`, `$input`, `$config`...) are accessible in Latte, e.g. `{$config}` for the config API variable.');
        $field->name = 'api_vars_available';
        $field->checked = (bool) $data['api_vars_available'];
        $wrapper->append($field);

        /** @var \ProcessWire\InputfieldCheckbox $field */
        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Auto-refresh templates (recompile)');
        $field->description = __('If enabled, templates are recompiled whenever the source code changes.');
        $field->name = 'auto_refresh';
        $field->checked = (bool) $data['auto_refresh'];
        $wrapper->append($field);

        /** @var \ProcessWire\InputfieldCheckbox $field */
        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Simplified path resolution');
        $field->description = __('Enable Blade-style dot syntax for directory traversal.') . ' ' .
            __('This will also prepend the root view folder to all paths.').  PHP_EOL . PHP_EOL .
            __("Both of these will render `site/templates/views/partials/navigation.latte`:") .  PHP_EOL . '  ' .
            __("Before: `{include '../partials/navigation.latte'}`") .  PHP_EOL . '  ' .
            __("After: `{include 'partials.navigation'}`");
        $field->name = 'simple_path_resolution';
        $field->checked = (bool) $data['simple_path_resolution'];
        $wrapper->append($field);

        /** @var \ProcessWire\InputfieldCheckbox $field */
        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Clear Latte cache');
        $field->description = __('Forces all templates to be recompiled by emptying the temp directory used by Latte.');
        $field->name = 'clear_temp';
        $field->checked = false;
        $wrapper->append($field);

        return $wrapper;
    }
}
