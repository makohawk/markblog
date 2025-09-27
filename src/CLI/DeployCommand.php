<?php
namespace CLI;

class DeployCommand
{
    public function execute(): void
    {
        $distDir = __DIR__ . '/../../dist';

        if (!is_dir($distDir)) {
            echo "No dist directory found. Run `markblog build` first.\n";
            return;
        }

        echo "Deploying to GitHub Pages...\n";
        
        // gh-pagesにpushする処理をここに書く

        echo "Done! (simulated)\n";
    }
}