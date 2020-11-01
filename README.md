# TemplateEngineLatte

[![Build Status](https://travis-ci.org/daun/TemplateEngineLatte.svg?branch=master)](https://travis-ci.org/daun/TemplateEngineLatte)
[![StyleCI](https://github.styleci.io/repos/308988747/shield?branch=master)](https://github.styleci.io/repos/308988747)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![ProcessWire 3](https://img.shields.io/badge/ProcessWire-3.x-orange.svg)](https://github.com/processwire/processwire)

A ProcessWire module adding [Latte](https://latte.nette.org/) to the [TemplateEngineFactory](https://github.com/wanze/TemplateEngineFactory).

## Requirements

* ProcessWire `3.0` or newer
* TemplateEngineFactory `2.0` or newer
* PHP >= `7.0` for version `2.x`, PHP >= `7.2.5` for version `3.x`
* Composer

## Installation

Execute the following command in the root directory of your ProcessWire installation:

```
composer require daun/template-engine-latte:^1.0
```

This will install the _TemplateEngineLatte_ and _TemplateEngineFactory_ modules in one step. Afterwards, don't forget
to enable Latte as engine in the _TemplateEngineFactory_ module's configuration.

> ℹ️ This module includes test dependencies. If you are installing on production with `composer install`, make sure to
pass the `--no-dev` flag to omit autoloading any unnecessary test dependencies!.

## Configuration

The module offers the following configuration:

* **`Template files suffix`** The suffix of the Latte template files, defaults to `latte`.
* **`Default layout file`** Layout that all views will extend from unless overwritten.
* **`Provide ProcessWire API variables in Latte templates`** API variables (`$pages`, `$input`, `$config`...)
are accessible in Latte,
e.g. `{$config}` for the config API variable.
* **`Simplified path resolution`** Enable Blade-style dot syntax for directory traversal. [See below](#simplified-path-resolution).
* **`Auto refresh templates (recompile)`** Recompile templates whenever the source code changes.

### Simplified Path Resolution

This option will enable two things:

* Allow dot syntax for directory traversal à la Blade.
* Prepend the base view directory to all paths, eliminating the need for endless `../` in subdirectories.

#### Example

Both of these will resolve to `site/templates/views/partials/navigation.latte`:

* Normal resolution: `{include '../../partials/navigation.latte'}`
* Simplified resolution enabled: `{include 'partials.navigation'}`

## Extending Latte

It is possible to extend Latte after it has been initialized by the module. Hook the method `TemplateEngineLatte::initLatte`
to register custom macros, filters, functions etc.

Here is an example how you can use the provided hook to add custom macros and filters.

```php
wire()->addHookAfter('TemplateEngineLatte::initLatte', function (HookEvent $event) {
    /** @var Latte\Engine */
    $latte = $event->arguments('latte');
    $compiler = $latte->getCompiler();

    // Add filter
    $latte->addFilter('lower', function ($str) { return strtolower($str); });

    // Add macro
    $compiler->addMacro('ifispage',
        'if (get_class(%node.word) === "ProcessWire\Page" && %node.word->id) {',
    '}');
});
```

> The above hook can be put in your `site/init.php` file. If you prefer to use modules, put it into the module's `init()`
method and make sure that the module is auto loaded.
