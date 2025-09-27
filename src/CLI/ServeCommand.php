<?php

declare(strict_types=1);

namespace CLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLIコマンド: ローカルPHP開発サーバーを起動してビルド済みサイトを確認する
 */
final class ServeCommand extends Command
{
    /**
     * コマンドの設定
     * - コマンド名: serve
     * - 説明: ローカルPHP開発サーバーを起動
     * - オプション: port (-p) 任意（デフォルト: 8080）
     */
    protected function configure(): void
    {
        $this
            ->setName("serve")
            ->setDescription('Start a local PHP development server for the built site.')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Port to run the server on',
                8080
            );
    }

    /**
     * コマンド実行処理
     * 1. オプションからポート番号を取得
     * 2. distディレクトリをWebルートとして設定
     * 3. distディレクトリが存在しなければエラー出力
     * 4. PHPビルトインサーバーを起動
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ポート番号を取得（デフォルトは8080）
        $port = (int) $input->getOption('port');

        $webroot = __DIR__ . '/../../dist';

        // distディレクトリが存在しない場合はエラーを返す
        if (!is_dir($webroot)) {
            $output->writeln("<error>Web root directory '{$webroot}' not found. Build the site first.</error>");
            return Command::FAILURE;
        }

        // 起動メッセージ表示
        $output->writeln("MarkBlog server is starting on http://localhost:{$port}");
        $output->writeln("Document root is: {$webroot}");
        $output->writeln("Press Ctrl-C to stop the server.");

        // PHPビルトインサーバー起動
        // escapeshellargでwebrootパスを安全にエスケープ
        passthru("php -S 0.0.0.0:{$port} -t " . escapeshellarg($webroot));

        return Command::SUCCESS;
    }
}
