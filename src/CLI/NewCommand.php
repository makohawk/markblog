<?php

namespace CLI;

class NewCommand
{
    /**
     * Markdownファイルの保存先ディレクトリのパス
     * @var string
     */
    private string $postsDir;

    /**
     * NewCommandのインスタンスを作成
     *
     * @param string|null $postsDir 投稿ファイルの保存先パス。
     *                              指定がなければデフォルトパス 'content/posts' を使用
     */
    public function __construct(?string $postsDir = null)
    {
        $this->postsDir = $postsDir ?? __DIR__ . '/../../content/posts';
    }

    /**
     * 新しい投稿のMarkdownファイルを作成
     *
     * @param string $title 投稿のタイトル
     * @return void
     */
    public function execute(string $title): void
    {
        // タイトルからYYYY-MM-DD-スラッグ形式のファイル名を生成
        $slugPart = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = date('Y-m-d') . '-' . $slugPart;
        $filename = $this->postsDir . '/' . $slug . '.md';

        // 既存ファイルがあれば中断
        if (file_exists($filename)) {
            echo "File already exists: $filename\n";
            return;
        }

        $currentDate = date('Y-m-d');
        $content = <<<MD
---
title: {$title}
date: {$currentDate}
---

この記事はまだ書かれていません。
MD;

        file_put_contents($filename, $content);

        echo "New post created: $filename\n";
    }
}