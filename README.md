# WP Content Framework (Admin module)

[![CI Status](https://github.com/wp-content-framework/admin/workflows/CI/badge.svg)](https://github.com/wp-content-framework/admin/actions)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

[WP Content Framework](https://github.com/wp-content-framework/core) のモジュールです。

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**

- [要件](#%E8%A6%81%E4%BB%B6)
- [インストール](#%E3%82%A4%E3%83%B3%E3%82%B9%E3%83%88%E3%83%BC%E3%83%AB)
  - [依存モジュール](#%E4%BE%9D%E5%AD%98%E3%83%A2%E3%82%B8%E3%83%A5%E3%83%BC%E3%83%AB)
  - [基本設定](#%E5%9F%BA%E6%9C%AC%E8%A8%AD%E5%AE%9A)
  - [画面の追加](#%E7%94%BB%E9%9D%A2%E3%81%AE%E8%BF%BD%E5%8A%A0)
  - [アクションリンクの追加](#%E3%82%A2%E3%82%AF%E3%82%B7%E3%83%A7%E3%83%B3%E3%83%AA%E3%83%B3%E3%82%AF%E3%81%AE%E8%BF%BD%E5%8A%A0)
  - [プラグイン情報リンクの追加](#%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%E6%83%85%E5%A0%B1%E3%83%AA%E3%83%B3%E3%82%AF%E3%81%AE%E8%BF%BD%E5%8A%A0)
- [Author](#author)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

# 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

# インストール

``` composer require wp-content-framework/admin ```

## 依存モジュール
* [controller](https://github.com/wp-content-framework/controller)
* [view](https://github.com/wp-content-framework/view)

## 基本設定
- configs/config.php

|設定値|説明|
|---|---|
|main_menu_title|管理画面のメニュー名（空でプラグイン名） \[default = '']|
|menu_image|管理画面のメニューアイコンを指定（空で歯車マーク） \[default = '']|
|suppress_setting_help_contents|ヘルプを非表示にするかどうかを設定 \[default = false]|
|setting_page_title|管理画面のメニューのタイトル \[default = Dashboard]|
|setting_page_priority|管理画面のメニューの優先順位 \[default = 0]|
|setting_page_slug|管理画面のメニューのslug \[default = setting]|
|action_links|アクションリンクの追加|
|plugin_row_meta|プラグイン情報リンクの追加|
|twitter|ツイッターのアカウントを指定（ダッシュボード 及び ヘルプに表示されます。空で未使用） \[default = '']|
|detail_url|詳細リンクを指定（ダッシュボードに表示されます。空で未使用） \[default = '']|
|github_repo|Githubのリポジトリを指定（ダッシュボード 及び エラー時の画面に表示されます。空で未使用） \[default = '']|
|contact_url|プラグインのお問い合わせ用のページのURLを指定（ダッシュボードのヘルプに表示されます。空で未使用） \[default = '']|
|github|Githubのアカウントを指定（ダッシュボードのヘルプに表示されます。空で未使用） \[default = '']|

## 画面の追加

- src/classes/controllers/admin に PHP ファイル (例：test.php) を追加
```
<?php

namespace Example_Plugin\Classes\Controllers\Admin;

if ( ! defined( 'EXAMPLE_PLUGIN' ) ) {
	exit;
}

class Test extends \WP_Framework\Classes\Controllers\Admin\Base {

	// タイトル
	public function get_page_title() {
		return 'Test';
	}

	// GET の時に行う動作
	protected function get_action() {

	}

	// POST の時に行う動作
	protected function post_action() {
		$aaa = $this->app->input->post( 'aaa' );
		// ...
	}

	// GET, POST 共通で行う動作
	protected function common_action() {
		// wp_enqueue_script('media-upload');
	}

	// view に渡す変数設定
	public function get_view_args() {
		return array(
			'test' => 'aaaa',
		);
	}
}
```

POST の時に行う動作は事前にnonce checkが行われます。

- src/views/admin に PHP ファイル (例：test.php) を追加
```
<?php

if ( ! defined( 'EXAMPLE_PLUGIN' ) ) {
	return;
}
/** @var \WP_Framework\Interfaces\Presenter $instance */
/** @var string $test */
?>

<?php $instance->form( 'open', $args ); ?>

<?php $instance->h( $test ); ?>
<?php $instance->form( 'input/submit', $args, array(
	'name'  => 'update',
	'value' => 'Update',
	'class' => 'button-primary'
) ); ?>

<?php $instance->form( 'close', $args ); ?>
```

- $instance
	- h：esc_html
	- dump：var_dump
	- id
	- form
	- url
	- img

- ヘルプの追加
	- src/classes/controllers/admin に追加した上記 PHP ファイル に以下を追記
```
protected function get_help_contents() {
    return array(
        array(
            'title' => 'Test',
            'view'  => 'test',
        )
    );
}
```

-
	- src/views/admin/help に PHP ファイル (例：test.php) を追加
```
<?php

if ( ! defined( 'EXAMPLE_PLUGIN' ) ) {
	return;
}
/** @var \WP_Framework\Interfaces\Presenter $instance */
?>

test
```

## アクションリンクの追加
![action links](https://raw.githubusercontent.com/technote-space/screenshots/master/wp-content-framework/201904121628.png)

配列で指定します。

|設定値|説明|
|---|---|
|url|リンク または リンクを返す関数 (string or closure, required)|
|label|リンクのテキスト または リンクのテキストを返す関数 (string or closure, required)|
|new_tab|新しいタブで開くかどうか (bool, optional)|

## プラグイン情報リンクの追加
![plugin row meta](https://raw.githubusercontent.com/technote-space/screenshots/master/wp-content-framework/201904121629.png)

配列で指定します。

|設定値|説明|
|---|---|
|url|リンク または リンクを返す関数 (string or closure, required)|
|label|リンクのテキスト または リンクのテキストを返す関数 (string or closure, required)|
|new_tab|新しいタブで開くかどうか (bool, optional)|

# Author
- [GitHub (Technote)](https://github.com/technote-space)
- [Blog](https://technote.space)
