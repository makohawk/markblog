<?php

declare(strict_types=1);

namespace CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLIコマンド: 新しいMarkdown投稿ファイルを作成する
 */
final class NewCommand extends Command
{
    /** 投稿Markdownファイルを格納するディレクトリ */
    private string $postsDir;

    /**
     * コンストラクタ
     * @param string $postsDir 投稿ディレクトリのパス（デフォルト: content/posts）
     */
    public function __construct(string $postsDir = __DIR__ . '/../../content/posts')
    {
        parent::__construct(); // Symfony Commandの初期化
        $this->postsDir = $postsDir;
    }

    /**
     * コマンドの設定
     * - コマンド名: new
     * - 説明: 新しいMarkdownブログ投稿を作成
     * - 引数: title (必須)
     * - オプション: category (-c), tag (-t)（複数指定可）
     */
    protected function configure(): void
    {
        $this
            ->setName('new')
            ->setDescription('Create a new Markdown blog post.')
            ->addArgument('title', InputArgument::REQUIRED, 'Post title')
            ->addOption(
                'category',
                'c',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Categories for the post'
            )
            ->addOption(
                'tag',
                't',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Tags for the post'
            );
    }

    /**
     * コマンド実行処理
     * 1. 引数とオプションから投稿情報を取得
     * 2. スラッグ生成（ファイル名用）
     * 3. ファイル存在チェック
     * 4. front matter付きMarkdownを作成してファイル保存
     * 5. 結果メッセージ出力
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 投稿タイトル
        $title = $input->getArgument('title');

        // カテゴリ・タグ（オプションが空なら空配列）
        $categories = $input->getOption('category') ?: [];
        $tags = $input->getOption('tag') ?: [];

        // スラッグ（ファイル名用）生成
        $slug = $this->makeSlug($title);

        // 投稿Markdownの保存先ファイルパス
        $filename = "{$this->postsDir}/{$slug}.md";

        // ファイルが既に存在する場合はエラー
        if (file_exists($filename)) {
            $output->writeln("<error>File already exists: {$filename}</error>");
            return Command::FAILURE;
        }

        // front matter付きMarkdownコンテンツ作成
        $content = $this->makeFrontMatter($title, $categories, $tags);

        file_put_contents($filename, $content);

        // 完了メッセージ
        $output->writeln("<info>New post created: {$filename}</info>");
        return Command::SUCCESS;
    }

    /**
     * 投稿タイトルからスラッグ生成
     * - 日付付き
     * - 半角英数字とハイフンのみ
     */
    private function makeSlug(string $title): string
    {
        $slugPart = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($title)));
        return sprintf('%s-%s', date('Y-m-d'), $slugPart);
    }

    /**
     * front matter付きMarkdownを作成
     * - YAML形式でタイトル、日付、カテゴリ、タグを出力
     * - 本文には「この記事はまだ書かれていません。」を初期状態として記載
     */
    private function makeFrontMatter(string $title, array $categories, array $tags): string
    {
        $date = date('Y-m-d');

        // 配列をカンマ区切りで出力（YAML配列形式）
        $categoriesYaml = implode(', ', array_map('trim', $categories));
        $tagsYaml = implode(', ', array_map('trim', $tags));

        return <<<MD
---
title: {$title}
date: {$date}
categories: [{$categoriesYaml}]
tags: [{$tagsYaml}]

この記事はまだ書かれていません。
MD;
    }
}
