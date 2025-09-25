<?php

declare(strict_types=1);

namespace Tests\CLI;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use CLI\NewCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

class NewCommandTest extends TestCase
{
    private string $tempPostsDir;

    /**
     * 各テスト実行前に一時ディレクトリを作成
     */
    protected function setUp(): void
    {
        $this->tempPostsDir = sys_get_temp_dir() . '/markblog_test_posts_' . uniqid();
        if (!is_dir($this->tempPostsDir)) {
            mkdir($this->tempPostsDir, 0777, true);
        }
    }

    /**
     * 各テスト実行後に一時ディレクトリを削除
     */
    protected function tearDown(): void
    {
        if (is_dir($this->tempPostsDir)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tempPostsDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($this->tempPostsDir);
        }
    }

    /**
     * 新規投稿Markdownファイルが正しく作成されるか
     */
    #[Test]
    public function createsNewPostFile(): void
    {
        $command = new NewCommand($this->tempPostsDir);
        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('new'));
        $title = 'My Awesome Post';

        $commandTester->execute([
            'title' => $title,
            '-c' => ['Tech', 'PHP'],
            '-t' => ['Example', 'MarkBlog']
        ]);

        $expectedSlug = 'my-awesome-post';
        $expectedFilename = date('Y-m-d') . '-' . $expectedSlug . '.md';
        $expectedFilePath = $this->tempPostsDir . '/' . $expectedFilename;

        $this->assertFileExists($expectedFilePath);

        $fileContent = file_get_contents($expectedFilePath);
        $this->assertStringContainsString("title: {$title}", $fileContent);
        $this->assertStringContainsString("date: " . date('Y-m-d'), $fileContent);
        $this->assertStringContainsString("categories: [Tech, PHP]", $fileContent);
        $this->assertStringContainsString("tags: [Example, MarkBlog]", $fileContent);

        // 出力にも成功メッセージが含まれる
        $this->assertStringContainsString('New post created:', $commandTester->getDisplay());
    }

    /**
     * 既存ファイルは上書きしない
     */
    #[Test]
    public function preventsOverwrite(): void
    {
        $title = 'Existing Post';
        $slug = date('Y-m-d') . '-existing-post.md';
        $filePath = $this->tempPostsDir . '/' . $slug;
        $originalContent = 'Original dummy content';
        file_put_contents($filePath, $originalContent);

        $command = new NewCommand($this->tempPostsDir);
        $application = new Application();
        $application->add($command);
        $commandTester = new CommandTester($application->find('new'));

        $commandTester->execute([
            'title' => $title
        ]);

        // 元の内容が上書きされていない
        $this->assertStringEqualsFile($filePath, $originalContent);

        // 出力にエラーが含まれる
        $this->assertStringContainsString('File already exists:', $commandTester->getDisplay());
    }
}
