<?php

declare(strict_types=1);

namespace Renderer;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * HTMLレンダリングクラス
 *
 * Twig テンプレートを用いて HTML を生成・保存する責務を持つ。
 */
final class Renderer
{
    private Environment $twig;

    /**
     * Renderer コンストラクタ
     *
     * @param string $themesDir テーマテンプレートのディレクトリ
     * @throws \RuntimeException テーマディレクトリが存在しない場合
     */
    public function __construct(string $themesDir = __DIR__ . '/../../themes')
    {
        if (!is_dir($themesDir)) {
            throw new \RuntimeException("Themes directory not found: {$themesDir}");
        }

        // Twig ローダーの初期化
        $loader = new FilesystemLoader($themesDir);
        $this->twig = new Environment($loader);
    }

    /**
     * HTMLをレンダリング
     *
     * @param string $template Twig テンプレート名 (例: default.html)
     * @param array $variables Twig に渡す変数配列
     * @return string 生成された HTML
     */
    public function render(string $template, array $variables = []): string
    {
        return $this->twig->render($template, $variables);
    }

    /**
     * HTML を指定パスに保存
     *
     * ディレクトリが存在しない場合は作成し、書き込み失敗時は例外を投げる
     *
     * @param string $path 保存先ファイルパス
     * @param string $html 保存する HTML 文字列
     * @throws \RuntimeException ディレクトリ作成または書き込み失敗時
     */
    public function save(string $path, string $html): void
    {
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            throw new \RuntimeException("Failed to create directory: {$dir}");
        }

        if (file_put_contents($path, $html) === false) {
            throw new \RuntimeException("Failed to write HTML to: {$path}");
        }
    }
}
