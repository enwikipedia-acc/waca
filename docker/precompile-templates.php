<?php
chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

$smarty = new \Smarty\Smarty();

$pluginsDir = dirname(__DIR__) . '/smarty-plugins';
foreach (new DirectoryIterator($pluginsDir) as $file) {
    if ($file->isDot() || $file->getExtension() !== 'php') {
        continue;
    }
    require_once $file->getPathname();
    [$type, $name] = explode('.', $file->getBasename('.php'), 2);
    switch ($type) {
        case 'modifier':
            $smarty->registerPlugin(\Smarty\Smarty::PLUGIN_MODIFIER, $name, 'smarty_modifier_' . $name);
            break;
        case 'function':
            $smarty->registerPlugin(\Smarty\Smarty::PLUGIN_FUNCTION, $name, 'smarty_function_' . $name);
            break;
    }
}

$smarty->compileAllTemplates('.tpl', true);
