#!/bin/bash

# git clone 後のSetup
# composer install
# cp .env.example .env
# php artisan key:generate
# php artisan config:clear#!/bin/bash
#


# setup.sh
#
# このスクリプトは、リポジトリをクローンした後に
# Laravel アプリケーションの初期設定を行います。
#

# --- 設定開始 ---
echo "アプリケーションのセットアップを開始します..."

# 1. Composer を使用して PHP の依存関係をインストール
echo "Composer の依存関係をインストール中..."
composer install

# 2. 環境設定ファイルのコピー
# .env ファイルが存在しない場合のみコピーを実行
if [ ! -f .env ]; then
  echo ".env.example から .env ファイルを作成中..."
  cp .env.example .env
else
  echo ".env ファイルは既に存在します。コピーをスキップします。"
fi

# 3. アプリケーションキーの生成
echo "アプリケーションキーを生成中..."
php artisan key:generate

# 4. 設定キャッシュのクリア
echo "設定キャッシュをクリア中..."
php artisan config:clear

# --- 設定完了 ---
echo "セットアップが正常に完了しました！"

exit 0
