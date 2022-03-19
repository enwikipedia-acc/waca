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
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownRenderingHelper
{
    private $config = [
        'html_input'         => 'escape',
        'allow_unsafe_links' => false,
        'max_nesting_level'  => 10
    ];

    private $blockRenderer;
    private $inlineRenderer;

    public function __construct()
    {
        $blockEnvironment = Environment::createCommonMarkEnvironment();
        $blockEnvironment->addExtension(new AttributesExtension());
        $blockEnvironment->mergeConfig($this->config);
        $this->blockRenderer = new MarkdownConverter($blockEnvironment);

        $inlineEnvironment = new Environment();
        $inlineEnvironment->addExtension(new AttributesExtension());
        $inlineEnvironment->addExtension(new InlinesOnlyExtension());
        $inlineEnvironment->mergeConfig($this->config);
        $this->inlineRenderer = new MarkdownConverter($inlineEnvironment);
    }

    public function doRender(string $content): string {
        return $this->blockRenderer->convertToHtml($content);
    }

    public function doRenderInline(string $content): string {
        return $this->inlineRenderer->convertToHtml($content);
    }

}