<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Helpers;

use League\CommonMark\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\MarkdownConverter;

class RenderingHelper
{
    public function doRender(string $content): string {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new AttributesExtension());
        $environment->mergeConfig([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10
        ]);

        $converter = new MarkdownConverter($environment);
        return $converter->convertToHtml($content);
    }

}