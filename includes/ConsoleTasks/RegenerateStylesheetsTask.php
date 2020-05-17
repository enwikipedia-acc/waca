<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\ConsoleTasks;

use Exception;
use ScssPhp\ScssPhp\Compiler;
use Waca\Tasks\ConsoleTaskBase;
use Waca\WebRequest;

class RegenerateStylesheetsTask extends ConsoleTaskBase
{
    const RESOURCES_GENERATED = 'resources/generated';

    public function execute()
    {
        $scss = new Compiler();
        $scss->setImportPaths('resources/scss');

        if (!$this->getSiteConfiguration()->getDebuggingTraceEnabled()) {
            $scss->setFormatter('ScssPhp\\ScssPhp\\Formatter\\Compressed');
            $scss->setSourceMap(Compiler::SOURCE_MAP_INLINE);
        }

        if (!is_dir(self::RESOURCES_GENERATED)) {
            mkdir(self::RESOURCES_GENERATED);
        }

        foreach (['bootstrap-main'] as $file) {
            file_put_contents(
                self::RESOURCES_GENERATED . '/' . $file . '.css',
                $scss->compile('/*! Do not edit this auto-generated file! */ @import "' . $file . '";'));
        }
    }
}
