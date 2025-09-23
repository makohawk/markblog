<?php

namespace CLI;

class ServeCommand
{
    /**
     * Webサーバーを起動
     *
     * @param int $port サーバーがリッスンするポート番号
     */
    public function execute(int $port): void
    {
        // ビルド済みHTMLが配置されるルートディレクトリ
        $webroot = __DIR__ . '/../../dist';

        if (!is_dir($webroot)) {
            echo "Web root directory '{$webroot}' not found.\n";
            echo "Please build your site first by running: markblog build\n";
            exit(1); // エラーで終了
        }

        echo "Markblog server is starting on http://localhost:{$port}\n";
        echo "Document root is: {$webroot}\n";
        echo "Press Ctrl-C to stop the server.\n";

        passthru("php -S 0.0.0.0:{$port} -t " . escapeshellarg($webroot));
        echo "http://localhost:8080";
    }
}