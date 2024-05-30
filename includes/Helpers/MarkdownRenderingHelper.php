<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 * ACC Development Team. Please see team.json for a list of contributors.     *
 *                                                                            *
 * This is free and unencumbered software released into the public domain.    *
 * Please see LICENSE.md for the full licencing statement.                    *
 ******************************************************************************/

namespace Waca\Helpers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownRenderingHelper
{
    private $config = [
        'html_input'         => 'escape',
        'allow_unsafe_links' => false,
        'max_nesting_level'  => 10,
        'table'              => [
            'wrap'                 => [
                'enabled'    => true,
                'tag'        => 'div',
                'attributes' => ['class' => 'markdown-table'],
            ],
            'alignment_attributes' => [
                'left'   => ['class' => 'text-left'],
                'center' => ['class' => 'text-center'],
                'right'  => ['class' => 'text-right'],
            ],
        ],
    ];

    private $blockRenderer;
    private $inlineRenderer;

    public function __construct()
    {
        $blockEnvironment = new Environment($this->config);
        $blockEnvironment->addExtension(new CommonMarkCoreExtension());
        $blockEnvironment->addExtension(new AttributesExtension());
        $blockEnvironment->addExtension(new TableExtension());
        $this->blockRenderer = new MarkdownConverter($blockEnvironment);

        $inlineEnvironment = new Environment($this->config);
        $inlineEnvironment->addExtension(new AttributesExtension());
        $inlineEnvironment->addExtension(new InlinesOnlyExtension());
        $this->inlineRenderer = new MarkdownConverter($inlineEnvironment);
    }

    public function doRender(string $content): string {
        return $this->blockRenderer->convert($content)->getContent();
    }

    public function doRenderInline(string $content): string {
        return $this->inlineRenderer->convert($content)->getContent();
    }

}