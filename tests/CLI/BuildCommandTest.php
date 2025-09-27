<?php

declare(strict_types=1);

namespace Tests\CLI;

use CLI\BuildCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class BuildCommandTest extends TestCase
{
    private string $tmpPostsDir;
    private string $tmpDistDir;

    protected function setUp(): void
    {
        $this->tmpPostsDir = sys_get_temp_dir() . '/test_posts_' . uniqid();
        $this->tmpDistDir  = sys_get_temp_dir() . '/test_dist_' . uniqid();

        mkdir($this->tmpPostsDir, 0777, true);
        mkdir($this->tmpDistDir, 0777, true);

        // Markdownサンプルファイルを作成
        file_put_contents($this->tmpPostsDir . '/sample.md', <<<MD
---
title: Test Post
date: 2025-09-27
categories: [Test]
tags: [PHP,Markdown]
---

# Hello World

This is a test post.
MD
        );

        file_put_contents($this->tmpPostsDir . '/sample2.md', <<<MD
---
title: Another Post
date: 2025-09-26
categories: [Example]
tags: [Markdown]
---

# Another Test

Content of another test post.
MD
        );
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tmpPostsDir . '/*.md'));
        rmdir($this->tmpPostsDir);

        array_map('unlink', glob($this->tmpDistDir . '/*.html'));
        rmdir($this->tmpDistDir);
    }

    public function testExecuteBuildsFilesAndIndex(): void
    {
        // Twig モック
        $loader = new ArrayLoader([
            'default.html' => '{{ title }} - {{ content }}',
        ]);
        $twig = new Environment($loader);

        // BuildCommand インスタンス
        $command = new BuildCommand();

        // Reflection で private プロパティを上書き
        $ref = new \ReflectionClass(BuildCommand::class);
        foreach (['postsDir' => $this->tmpPostsDir, 'distDir' => $this->tmpDistDir, 'twig' => $twig] as $prop => $val) {
            $property = $ref->getProperty($prop);
            $property->setAccessible(true);
            $property->setValue($command, $val);
        }

        // Symfony Console でテスト
        $application = new Application();
        $application->add($command);

        $tester = new CommandTester($application->find('build'));
        $status = $tester->execute([]);

        $this->assertSame(0, $status);

        // 個別記事HTML生成確認
        foreach (['sample.html', 'sample2.html'] as $file) {
            $path = $this->tmpDistDir . '/' . $file;
            $this->assertFileExists($path);

            $content = file_get_contents($path);
            $this->assertStringContainsString(' - ', $content);
        }

        // index.html生成確認
        $indexPath = $this->tmpDistDir . '/index.html';
        $this->assertFileExists($indexPath);
        $indexContent = file_get_contents($indexPath);

        $this->assertStringContainsString('Blog Index', $indexContent);
        $this->assertStringContainsString('Test Post', $indexContent);
        $this->assertStringContainsString('Another Post', $indexContent);
    }
}
