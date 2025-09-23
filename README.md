# MarkBlog

超軽量Markdownブログジェネレーター（開発中）
シンプルなCLIツールとして、**未経験や学習中のエンジニア**がブログ記事の作成、ビルド、そしてプレビューまでの一連の流れを体験できるよう設計しています。

<a href="https://github.com/makohawk/markblog/actions/workflows/test.yml"><img src="https://github.com/makohawk/markblog/actions/workflows/test.yml/badge.svg" alt="CI Status Badge"></a>
   <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.3+-777BB4" alt="PHP 8.3.25"></a>

## 🎯 プロジェクトの目的

未経験や学習中のエンジニアが「フレームワークを構築する側」の視点を体験できるように、最小限の機能でブログ作成・公開プロセスを学べるツールとして開発しました。複雑な要素を極力排し、CLIツールの開発や静的サイト生成の**本質的な部分**に触れることに焦点を当てています。

## 🛠 主な機能

-   **記事作成**: Markdown形式で新しいブログ記事を生成します。
-   **サイトビルド**: Markdown記事を静的HTMLファイルに変換し公開準備をします。
-   **ローカルプレビュー**: 開発用のビルトインサーバーでローカル環境でのブログ表示を確認できます。
-   **デプロイ**: GitHub Pagesなどへの簡単なデプロイを体験できます。

## 🚀 コマンドリファレンス

markblogで利用可能な主なコマンドは以下の通りです。

```bash
php markblog new "記事タイトル"   # 新しいMarkdown記事を作成します（例: php markblog new "初めてのブログ"）
php markblog build                # Markdown記事をHTMLファイルに変換し`dist/`ディレクトリに出力します
php markblog serve 8080           # ローカル開発サーバーを起動しhttp://localhost:8080 でプレビューします
php markblog deploy               # ビルド済みサイトをGitHub Pagesなどへデプロイします
```