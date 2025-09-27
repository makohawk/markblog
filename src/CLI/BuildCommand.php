<?php

declare(strict_types=1);

namespace CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Markdown\Parser;
use Renderer\Renderer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * BuildCommand
 *
 * Markdown記事をHTMLに変換し、個別ページとindexページを生成するコマンド。
 */
final class BuildCommand extends Command
{
    private string $postsDir; // Markdown記事が置かれたディレクトリ
    private string $distDir;  // ビルド後のHTML出力ディレクトリ
    private Environment $twig; // Twigテンプレートエンジン

    /**
     * コンストラクタでTwig初期化
     */
    public function __construct()
    {
        parent::__construct();

        $this->postsDir = __DIR__ . '/../../content/posts';
        $this->distDir  = __DIR__ . '/../../dist';

        // Twigのテンプレートローダーを指定（themesディレクトリ）
        $loader = new FilesystemLoader(__DIR__ . '/../../themes');
        $this->twig = new Environment($loader);
    }

    /**
     * コマンド設定
     */
    protected function configure(): void
    {
        $this
            ->setName('build')
            ->setDescription('Build all Markdown posts to HTML including index page.');
    }

    /**
     * コマンド実行処理
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // distディレクトリがなければ作成
        if (!is_dir($this->distDir) && !mkdir($this->distDir, 0777, true)) {
            $output->writeln("<error>Failed to create dist directory: {$this->distDir}</error>");
            return Command::FAILURE;
        }

        // Markdownファイルを収集
        $files = glob("{$this->postsDir}/*.md");
        if (!$files) {
            $output->writeln("<comment>No Markdown files found in {$this->postsDir}.</comment>");
            return Command::SUCCESS;
        }

        $parser = new Parser(); // Markdown→HTML変換用
        $postsData = [];        // index生成用のデータ格納

        foreach ($files as $filePath) {
            $markdownContent = file_get_contents($filePath);
            if ($markdownContent === false) continue;

            // YAML front-matter 解析
            $metadata = $this->extractMetadata($markdownContent);
            $title = $metadata['title'] ?? basename($filePath, '.md');
            $categories = $metadata['categories'] ?? [];
            $tags = $metadata['tags'] ?? [];
            $date = $metadata['date'] ?? '';

            // Markdown → HTML
            $htmlBody = $parser->toHtml($markdownContent);

            // 個別記事 HTML 出力
            $html = $this->twig->render('default.html', [
                'title' => $title,
                'date' => $date,
                'categories' => $categories,
                'tags' => $tags,
                'content' => $htmlBody
            ]);

            $outputPath = "{$this->distDir}/" . basename($filePath, '.md') . ".html";
            file_put_contents($outputPath, $html);
            $output->writeln("<info>Built: {$outputPath}</info>");

            // index用データ収集
            $postsData[] = [
                'title' => $title,
                'date' => $date,
                'categories' => $categories,
                'tags' => $tags,
                'excerpt' => $this->getExcerpt($htmlBody),
                'file' => basename($filePath, '.md') . ".html"
            ];
        }

        // index.html の生成
        $indexContent = $this->generateIndexContent($postsData);
        $indexHtml = $this->twig->render('default.html', [
            'title' => 'Blog Index',
            'date' => '',
            'categories' => [],
            'tags' => [],
            'content' => $indexContent
        ]);
        file_put_contents("{$this->distDir}/index.html", $indexHtml);
        $output->writeln("<info>Index page generated: {$this->distDir}/index.html</info>");

        return Command::SUCCESS;
    }

    /**
     * MarkdownからYAML front-matterを解析してメタデータを取得
     */
    private function extractMetadata(string $markdown): array
    {
        if (!preg_match('/^---\s*\n(.*?)\n---\s*/s', $markdown, $matches)) return [];

        $metadata = [];
        foreach (explode("\n", trim($matches[1])) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // key: value の形式にマッチ
            if (preg_match('/^([\w_-]+):\s*(.+)$/', $line, $kv)) {
                $key = $kv[1];
                $value = trim($kv[2]);

                // 配列対応
                if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
                    $value = array_map('trim', explode(',', trim($value, '[]')));
                }

                $metadata[$key] = $value;
            }
        }

        // 日付がなければ今日の日付を設定
        if (!isset($metadata['date'])) {
            $metadata['date'] = date('Y-m-d');
        }

        return $metadata;
    }

    /**
     * HTML本文から抜粋テキストを生成
     */
    private function getExcerpt(string $html, int $length = 150): string
    {
        $text = strip_tags($html);
        if (mb_strlen($text) > $length) {
            $text = mb_substr($text, 0, $length) . '...';
        }
        return $text;
    }

    /**
     * indexページ用のHTMLリストを生成
     */
    private function generateIndexContent(array $postsData): string
    {
        // 日付順で降順ソート
        usort($postsData, fn($a, $b) => strcmp($b['date'], $a['date']));

        $listHtml = '';
        foreach ($postsData as $post) {
            $categories = implode(', ', $post['categories']);
            $tags = implode(', ', $post['tags']);

            $listHtml .= <<<HTML
<hr />
<h2><a href="{$post['file']}">{$post['title']}</a></h2>
<p>Date: {$post['date']} | Categories: {$categories} | Tags: {$tags}</p>
<p>{$post['excerpt']}</p>

HTML;
        }
        return $listHtml;
    }
}
