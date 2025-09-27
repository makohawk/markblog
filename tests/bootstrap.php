<?php
require __DIR__ . '/../vendor/autoload.php';

// テスト用 dist/ ディレクトリ作成
$testDistDir = __DIR__ . '/dist';
if (!is_dir($testDistDir)) {
    mkdir($testDistDir, 0777, true);
}
putenv("MARKBLOG_TEST_DIST={$testDistDir}");
