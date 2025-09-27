<?php

declare(strict_types=1);

namespace Markdown;

use League\CommonMark\CommonMarkConverter;

/**
 * Markdown パーサークラス
 * 
 * Markdown 文字列を HTML に変換する機能を提供。
 * CommonMark 標準に準拠しセキュリティ考慮も行う。
 */
final class Parser
{
    /**
     * @param CommonMarkConverter $converter Markdown → HTML 変換器
     * 
     * html_input は 'strip' に設定し、HTML タグは除去
     * allow_unsafe_links は false に設定し危険なリンクを無効化
     */
    public function __construct(
        private readonly CommonMarkConverter $converter = new CommonMarkConverter([

            // HTML タグを除去して安全に変換
            'html_input' => 'strip',

            // javascript: 等の危険リンクを無効化
            'allow_unsafe_links' => false,
        ])
    ) {}

    /**
     * Markdown 文字列を HTML に変換
     *
     * @param string $markdown Markdown 文字列
     * @return string 変換後の HTML
     */
    public function toHtml(string $markdown): string
    {
        return $this->converter->convert($markdown)->getContent();
    }
}
