<?php
/*
Plugin Name: yoast_premium_adapter
Plugin URI: https://yoast.fukugyo-knowhow.com/
Description:Yoast SEOの固定ページ、投稿およびカスタム投稿のタイトル・記事、およびSEOタイトル・デスクリプションを日本語対応します。サークル会員のみがご利用いただけます。https://note.com/settings/circle
Author: 副業ノウハウ
Version: 0.4.3
Author URI: https://note.com/subjob5000/circle
License: GPL2
*/
?>
<?php
/*  Copyright 2017 T.Fukuda (email : fkdsapporo@gmail.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// 管理画面（ダッシュボード）アクションフック
add_action('add_meta_boxes', 'wordpress_wakachi_widget_setup');
add_action('save_post', 'wordpress_wakachi_widget_save');
// 管理画面（ダッシュボード）設定画面の追加
add_action('admin_menu', 'wordpress_wakachi_plugin_menu');
add_action('admin_init', 'wordpress_wakachi_plugin_init');
// 管理画面（ダッシュボード）javascript登録
add_action('admin_enqueue_scripts', 'wordpress_wakachi_plugin_init_js');
 

?>
<?php
// 管理画面（ダッシュボード）アクションフック
add_action('add_meta_boxes', 'kerorin23_yoastseo_adapter_widget_setup');
add_action('save_post', 'kerorin23_yoastseo_adapter_widget_save');
?>
<?php
function add_cors_http_header(){
    header("Access-Control-Allow-Origin: *");
}
add_action('init','add_cors_http_header');

// 投稿・固定ページ表示のフィルターフック
add_filter('diver_loop_excerpt', 'kerorin23_yoastseo_adapter_display_content', 9);
add_filter('excerpt', 'kerorin23_yoastseo_adapter_display_content', 9);

add_filter('the_content', 'kerorin23_yoastseo_adapter_display_content', 9);
add_filter('the_title', 'kerorin23_yoastseo_adapter_display_title', 9, 2);
// Yoast SEOが出力する箇所のフィルターフック
add_filter('wpseo_title', 'kerorin23_yoastseo_adapter_wpseo_title', 9);
add_filter('wpseo_metadesc', 'kerorin23_yoastseo_adapter_wpseo_description', 9);
// ↓ Yoast SEOを使用していない場合、pre_get_document_titleとwp_titleはWordPressのバージョンによりどちらか一方が呼ばれる。
// 必要な方のコメントアウトを解除してください。
// add_filter('pre_get_document_title', 'kerorin23_yoastseo_wp_title', 9, 2);
// add_filter('wp_title', 'kerorin23_yoastseo_wp_title', 9, 2);
?>
<?php $KERORIN23_YOASTSEO_ADAPTER_DEBUG = false ?>
<?php
// require 'path/to/plugin-update-checker/plugin-update-checker.php';
include( plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php');
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://yoast.fukugyo-knowhow.com/assets/plugin.json',
	__FILE__, //Full path to the main plugin file or functions.php.
	'yoast_premium_adapter'
);
//add extra fields to category edit form hook
add_filter( 'edit_category_form_fields', 'extra_category_wakachi_fields');
//add extra fields to category edit form callback function
function extra_category_wakachi_fields($tag) {    //check for existing featured ID
	
    global $post;
	wordpress_wakachi_widget_category($post);
}
function wordpress_wakachi_widget_category($post) {
	wp_nonce_field( 'wordpress_wakachi_widget_data', 'wordpress_wakachi_widget_nonce' );
	
	$yahoo_appid = get_option( 'wakachi_yahoo_appid' );
	$license_key = get_option( 'wakachi_license_key' );
	// $ajax_url = plugins_url('wordpress_wakachi_plugin_ajax', __FILE__);
	$ajax_url = "https://yoast.fukugyo-knowhow.com/wordpress_wakachi_plugin_ajax";
	$site_url=site_url();
	// mod-rewrite対応
	// $ajax_url = str_replace( "wp-content", "core", $ajax_url );

	echo <<<EOS
			<style>
				.wakachi_category{
					width:15vw;
					border:1px solid;
					border-radius:5px;
					padding:10px;
					z-index:100000;
					position:fixed;
					top:200px;
					right:10px;
				}
				#edittag {
					width: 63vw;
				}
				.postbox-header .hndle {
					flex-grow: 1;
					display: flex;
					justify-content: center;
					align-items: center;
				}
			</style>
		 	<div id="wordpress_wakachi_widget" class="wakachi_category">
				<div class="postbox-header"><h2 class="hndle ui-sortable-handle">分かち書き</h2></div>
				<div class="inside">
					<input type="hidden" id="wordpress_wakachi_widget_nonce" name="wordpress_wakachi_widget_nonce" value="e2f68fe602"><input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/post-new.php"> 
					<p>
						<span style="font-size: small;">投稿およびSEOの入力内容を分かち書きしたり、結合文の状態に戻します。</span>
					</p>
					<div id="wordpress_wakachi_widget_button">
						<p>
							<a href="#wordpress_wakachi_widget" class="button" id="wordpress_wakachi_widget_btn_wakachi">分かち書き</a>&nbsp;&nbsp;
							<a href="#wordpress_wakachi_widget" class="button" id="wordpress_wakachi_widget_btn_join">結 合</a>
						</p>
					</div>
					<a href="#wordpress_wakachi_widget" role="button" id="wordpress_wakachi_widget_select_open" class="hide-if-js" style="display: none;">分かち対象を指定</a>
					<span id="wordpress_wakachi_widget_select_open_desc">分かち対象を指定</span><br />
					<div id="wordpress_wakachi_widget_select" style="padding: 6px;">
						<input id="wordpress_wakachi_enable_title" name="wordpress_wakachi_enable_title" type="checkbox" value="1" checked/>
						<label for="wordpress_wakachi_enable_title">タイトル</label>
						<br />
						<input id="wordpress_wakachi_enable_content" name="wordpress_wakachi_enable_content" type="checkbox" value="1" checked />
						<label for="wordpress_wakachi_enable_content">本文</label>
						<br />
						<input id="wordpress_wakachi_enable_seotitle" name="wordpress_wakachi_enable_seotitle" type="checkbox" value="1"  />
						<label for="wordpress_wakachi_enable_seotitle">SEOタイトル</label>
						<br />
						<input id="wordpress_wakachi_enable_seodesc" name="wordpress_wakachi_enable_seodesc" type="checkbox" value="1"  />
						<label for="wordpress_wakachi_enable_seodesc">SEOデスクリプション</label>
						<br />
						<a href="#wordpress_wakachi_widget" role="button" id="wordpress_wakachi_widget_select_close">隠す</a>
					</div>
					<span id="wordpress_wakachi_widget_yahoo_appid" style="display: none;">{$yahoo_appid}</span>
					<span id="wordpress_wakachi_widget_license_key" style="display: none;">{$license_key}</span>
					<span id="wordpress_wakachi_widget_ajax_url" style="display: none;">{$ajax_url}</span>
					<span id="wordpress_wakachi_widget_site_url" style="display: none;">{$site_url}</span>
					
				<!-- Begin Yahoo! JAPAN Web Services Attribution Snippet -->
					<div style="text-align: left; padding: 12px 0px 0px 0px;">
						<a href="https://developer.yahoo.co.jp/about" target="extlink"><img src="https://s.yimg.jp/images/yjdn/yjdn_attbtn2_105_17.gif" width="105" height="17" title="Webサービス by Yahoo! JAPAN" alt="Webサービス by Yahoo! JAPAN" border="0"></a>
					</div>
				<!-- End Yahoo! JAPAN Web Services Attribution Snippet -->
					<div id="wordpress_wakachi_widget_div_msg" style="position:absolute; width: 40%; height: 25%; left: 59%; top: 20%;">
						<span id="wordpress_wakachi_widget_msg" style="font-size: small; color: #AAAAAA;"></span>
					</div>
				</div>
		 </div>
EOS;
}
?>
<?php $WORDPRESS_WAKACHI_PLUGIN_DEBUG = false ?>
<?php
/** 管理画面（ダッシュボード）のウィジェット */

function wordpress_wakachi_widget_setup() {
	
	$custom_types = get_post_types( array('public' => true, '_builtin' => false), 'names', 'and' );
	$screens = array( 'post','page' ) + $custom_types;
	
	foreach ( $screens as $screen ) {
		
		add_meta_box(
			'wordpress_wakachi_widget',
			'分かち書き',
			'wordpress_wakachi_widget',
			$screen,
			'side'
		);
		
	}
}

function wordpress_wakachi_widget($post) {
	wp_nonce_field( 'wordpress_wakachi_widget_data', 'wordpress_wakachi_widget_nonce' );

	$checked_title = (get_post_meta( $post->ID, 'wordpress_wakachi_enable_title', true ) == '0') ? '' : 'checked="checked"';
	$checked_content = (get_post_meta( $post->ID, 'wordpress_wakachi_enable_content', true ) == '0') ? '' : 'checked="checked"';
	$checked_seotitle = (get_post_meta( $post->ID, 'wordpress_wakachi_enable_seotitle', true ) == '1') ? 'checked="checked"' : '';
	$checked_seodesc = (get_post_meta( $post->ID, 'wordpress_wakachi_enable_seodesc', true ) == '1') ? 'checked="checked"' : '';
	
	$yahoo_appid = get_option( 'wakachi_yahoo_appid' );
	$license_key = get_option( 'wakachi_license_key' );
// 	$ajax_url = plugins_url('wordpress_wakachi_plugin_ajax', __FILE__);
	$ajax_url = "https://yoast.fukugyo-knowhow.com/wordpress_wakachi_plugin_ajax";
	$site_url=site_url();
	// $ajax_url = "http://localhost/exe/wordpress_wakachi_plugin_ajax";
	// $ajax_url=site_url("wordpress_wakachi_plugin_ajax");
	// mod-rewrite対応
	// $ajax_url = str_replace( "wp-content", "core", $ajax_url );

    echo <<<EOS
         <p>
	      <span style="font-size: small;">投稿およびSEOの入力内容を分かち書きしたり、結合文の状態に戻します。</span>
	     </p>
	     <div id="wordpress_wakachi_widget_button">
	       <p>
	         <a href="#wordpress_wakachi_widget" class="button" id="wordpress_wakachi_widget_btn_wakachi">分かち書き</a>&nbsp;&nbsp;<a href="#wordpress_wakachi_widget" class="button" id="wordpress_wakachi_widget_btn_join">結 合</a>
	       </p>
	     </div>
	     <a href="#wordpress_wakachi_widget" role="button" id="wordpress_wakachi_widget_select_open" class="hide-if-js" style="display: none;">分かち対象を指定</a>
	     <span id="wordpress_wakachi_widget_select_open_desc">分かち対象を指定</span><br />
	     <div id="wordpress_wakachi_widget_select" style="padding: 6px;">
	       <input id="wordpress_wakachi_enable_title" name="wordpress_wakachi_enable_title" type="checkbox" value="1" {$checked_title} />
	       <label for="wordpress_wakachi_enable_title">タイトル</label>
	       <br />
	       <input id="wordpress_wakachi_enable_content" name="wordpress_wakachi_enable_content" type="checkbox" value="1" {$checked_content} />
	       <label for="wordpress_wakachi_enable_content">本文</label>
	       <br />
	       <input id="wordpress_wakachi_enable_seotitle" name="wordpress_wakachi_enable_seotitle" type="checkbox" value="1" {$checked_seotitle} />
	       <label for="wordpress_wakachi_enable_seotitle">SEOタイトル</label>
	       <br />
	       <input id="wordpress_wakachi_enable_seodesc" name="wordpress_wakachi_enable_seodesc" type="checkbox" value="1" {$checked_seodesc} />
	       <label for="wordpress_wakachi_enable_seodesc">SEOデスクリプション</label>
	       <br />
	       <a href="#wordpress_wakachi_widget" role="button" id="wordpress_wakachi_widget_select_close">隠す</a>
	     </div>
	     <span id="wordpress_wakachi_widget_yahoo_appid" style="display: none;">{$yahoo_appid}</span>
	     <span id="wordpress_wakachi_widget_license_key" style="display: none;">{$license_key}</span>
		 <span id="wordpress_wakachi_widget_ajax_url" style="display: none;">{$ajax_url}</span>
		 <span id="wordpress_wakachi_widget_site_url" style="display: none;">{$site_url}</span>
	     
<!-- Begin Yahoo! JAPAN Web Services Attribution Snippet -->
         <div style="text-align: left; padding: 12px 0px 0px 0px;">
           <a href="https://developer.yahoo.co.jp/about" target="extlink"><img src="https://s.yimg.jp/images/yjdn/yjdn_attbtn2_105_17.gif" width="105" height="17" title="Webサービス by Yahoo! JAPAN" alt="Webサービス by Yahoo! JAPAN" border="0"></a>
         </div>
<!-- End Yahoo! JAPAN Web Services Attribution Snippet -->
		<div id="wordpress_wakachi_widget_div_msg" style="position:absolute; width: 40%; height: 25%; left: 59%; top: 20%;">
		  <span id="wordpress_wakachi_widget_msg" style="font-size: small; color: #AAAAAA;"></span>
		</div>
EOS;
}
function wordpress_wakachi_widget_save($post_id) {
	// Check if our nonce is set.
	if ( ! isset( $_POST['wordpress_wakachi_widget_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['wordpress_wakachi_widget_nonce'], 'wordpress_wakachi_widget_data' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$enabled = isset($_POST['wordpress_wakachi_enable_title']) ? $_POST['wordpress_wakachi_enable_title'] : '0';
	update_post_meta( $post_id, 'wordpress_wakachi_enable_title', $enabled );
	$enabled = isset($_POST['wordpress_wakachi_enable_content']) ? $_POST['wordpress_wakachi_enable_content'] : '0';
	update_post_meta( $post_id, 'wordpress_wakachi_enable_content', $enabled );
	$enabled = isset($_POST['wordpress_wakachi_enable_seotitle']) ? $_POST['wordpress_wakachi_enable_seotitle'] : '0';
	update_post_meta( $post_id, 'wordpress_wakachi_enable_seotitle', $enabled );
	$enabled = isset($_POST['wordpress_wakachi_enable_seodesc']) ? $_POST['wordpress_wakachi_enable_seodesc'] : '0';
	update_post_meta( $post_id, 'wordpress_wakachi_enable_seodesc', $enabled );
}
?>
<?php
	/** 設定画面とメニュー */
	function wordpress_wakachi_plugin_init() {
		// Yahoo アプリケーションID
		register_setting( 'wordpress_wakachi_plugin_setting', 'wakachi_yahoo_appid', 'wordpress_wakachi_plugin_setting_validate' );
		register_setting( 'wordpress_wakachi_plugin_setting', 'wakachi_license_key', 'wordpress_wakachi_plugin_setting_validate' );
		
		add_settings_section( 'wordpress_wakachi_plugin_setting_section1', '', 'wordpress_wakachi_plugin_setting_section1_desc', 'wordpress_wakachi_plugin_setting' );
		add_settings_field( 'yahoo_appid', 'YahooアプリケーションID', 'wordpress_wakachi_plugin_setting_yahoo_appid', 'wordpress_wakachi_plugin_setting', 'wordpress_wakachi_plugin_setting_section1');
		add_settings_field( 'license_key', 'ライセンスキー', 'wordpress_wakachi_plugin_setting_license_key', 'wordpress_wakachi_plugin_setting', 'wordpress_wakachi_plugin_setting_section1');
		
	}
	
	function wordpress_wakachi_plugin_menu() {
		add_options_page('Yoast_premium_adapter設定', 'Yoast_premium_adapter設定', 'manage_options', 'wordpress_wakachi_plugin_setting', 'wordpress_wakachi_plugin_setting_page');
	}
	
	function wordpress_wakachi_plugin_setting_page() {
		echo <<<EOS1
		<div class="wrap">
		  <h2>Yoast_premium_adapter設定</H2>
		  <form method="post" action="options.php">
EOS1;
		
		settings_fields( 'wordpress_wakachi_plugin_setting' );
		do_settings_sections( 'wordpress_wakachi_plugin_setting' );
		submit_button();
		echo <<<EOS2
		  </form>
		</div>
EOS2;

		// $ajax_url = plugins_url('wordpress_wakachi_plugin_ajax', __FILE__);
		$ajax_url = "https://yoast.fukugyo-knowhow.com/wordpress_wakachi_plugin_ajax";
		// $ajax_url_core = str_replace( "wp-content", "core", $ajax_url );
		$abs_path = ABSPATH;
		$htaccesses = array();
		$result_cd = 0;
		// exec("find {$abs_path} -name .htaccess", $htaccesses, $result_cd);
		
		echo <<<EOS3
		<div class="wrap">
		<dl>
			<dt>ライセンスキーの確認はこちら</dt>
			<dd><a href="https://yoast.fukugyo-knowhow.com" target="_blank">https://yoast.fukugyo-knowhow.com</a></dd>
			<dt>新規ユーザー登録の申請はこちら</dt>
			<dd><a href="https://yoast.fukugyo-knowhow.com/register" target="_blank">http://yoast.fukugyo-knowhow.com/register</a></dd>
			<dt>パスワードを忘れた埸合の変更はこちら</dt>
			<dd><a href="https://yoast.fukugyo-knowhow.com/forgot" target="_blank">http://yoast.fukugyo-knowhow.com/forgot</a></dd>
			
		</dl>
		<br />
		<span>.htaccess情報（アクセス権限、mod-rewrite設定確認用：開発者向け）</span><br />
		<textarea cols="70" rows="12" disabled="disabled">
EOS3;
		if ( $result_cd == 0 ) {
			foreach ( $htaccesses as $file ) {
				print( "{$file} \n" );
				print( shell_exec("cat {$file} \n") );
				print( "\n" );
			}
		} else {
			print( ".htaccess file information not available: {$result_cd}."  );
		}
		echo <<<EOS4
		</textarea>
		</div>
EOS4;
	}
	
	function wordpress_wakachi_plugin_setting_section1_desc() {
		echo <<<EOS
		<p>
		  <span style="font-sie:small;">YahooアプリケーションIDを入力してください。YahooアプリケーションIDの詳細は<a href="https://www.yahoo-help.jp/app/answers/detail/p/537/a_id/43397" target="extlink">こちら</a>をご覧ください。</span>
		</p>
EOS;
	}
	
	function wordpress_wakachi_plugin_setting_yahoo_appid() {
		$yahoo_appid = get_option( 'wakachi_yahoo_appid' );
		echo <<<EOS
		<input type="text" id="wordpress_wakachi_plugin_yahoo_appid" name="wakachi_yahoo_appid" value ="{$yahoo_appid}" size="50" maxlength="80" />
EOS;
	} 
	function wordpress_wakachi_plugin_setting_license_key() {
		$license_key = get_option( 'wakachi_license_key' );
		echo <<<EOS
		<input type="text" id="wordpress_wakachi_plugin_license_key" name="wakachi_license_key" value ="{$license_key}" size="50" maxlength="80" />
EOS;
	} 
	
	function wordpress_wakachi_plugin_setting_validate( $input ) {
		return $input;
	}
?>
<?php
	/** javascript */
	function wordpress_wakachi_plugin_init_js($hook) {
		if ( 'post-new.php' != $hook && 'post.php' != $hook && 'term.php' != $hook ) {
			return;
		}
		if('term.php'==$hook){
			wp_register_script( 'wordpress_wakachi_plugin_category_js', plugins_url('/js/wordpress_wakachi_plugin_category.js', __FILE__), array('jquery'), false, true );
			wp_enqueue_script( 'wordpress_wakachi_plugin_category_js' );
			return;
		}
		wp_register_script( 'wordpress_wakachi_plugin_js', plugins_url('/js/wordpress_wakachi_plugin.js', __FILE__), array('jquery'), false, true );
		wp_enqueue_script( 'wordpress_wakachi_plugin_js' );
	}
?>
<?php
/** 管理画面（ダッシュボード）のウィジェット */
function kerorin23_yoastseo_adapter_widget_setup() {
	$custom_types = get_post_types( array('public' => true, '_builtin' => false), 'names', 'and' );
	$screens = array( 'post', 'page' ) + $custom_types;
	
	foreach ( $screens as $screen ) {
		add_meta_box(
			'kerorin23_yoastseo_adapter_widget',
			'半角スペースを繋げる',
			'kerorin23_yoastseo_adapter_widget',
			$screen,
			'side'
		);
	}
}

function kerorin23_yoastseo_adapter_widget($post) {
	wp_nonce_field( 'kerorin23_yoastseo_adapter_widget_data', 'kerorin23_yoastseo_adapter_widget_nonce' );

	$enabled = get_post_meta( $post->ID, '_kerorin23_yoastseo_adapter_widget_enabled', true );
	$checked = ($enabled === '') ? '' : 'checked="checked"';
	
        echo <<<EOS
         <p>
	      <span style="font-size: small;">半角英数記号の間<strong>以外</strong>にある単一の半角スペースを表示の際に削除します。</span>
	     </p>
	     <p>
	      <label for="kerorin23_yoastseo_adapter_widget_enabled">有効：</label>
              <input id="kerorin23_yoastseo_adapter_widget_enabled"
		 name="kerorin23_yoastseo_adapter_widget_enabled" type="checkbox"
		 value="1" {$checked} />
	     </p>
         <p>
	      <span style="font-size: small;">意図的に半角スペースを表示したい場合は以下のようにしてください。</span>
	     </p>
	     <dl>
	       <dt style="letter-spacing:-0.05em;">記事タイトル、記事部分：</dt>
	       <dd style="margin-left: 2em;">半角スペースを２つ連続させる</dd>
	       <dt style="letter-spacing:-0.05em;">yoast SEOのタイトル、デスクリプション：</dt>
	       <dd style="margin-left: 2em;">半角スペースの代りに半角アスタ「*」を入れる</dd>
	     </dl>
EOS;
}

function kerorin23_yoastseo_adapter_widget_save($post_id) {
	// Check if our nonce is set.
	if ( ! isset( $_POST['kerorin23_yoastseo_adapter_widget_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( $_POST['kerorin23_yoastseo_adapter_widget_nonce'], 'kerorin23_yoastseo_adapter_widget_data' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$enabled = isset($_POST['kerorin23_yoastseo_adapter_widget_enabled']) ? $_POST['kerorin23_yoastseo_adapter_widget_enabled'] : '';
	update_post_meta( $post_id, '_kerorin23_yoastseo_adapter_widget_enabled', $enabled );
}
?>
<?php 
/** 表示処理 */
function kerorin23_yoastseo_adapter_wpseo_title($title) {
	if ( !is_single() && !is_page() && !is_preview()) {
		return $title . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing wpseo_title]' : '');
	}
	
	$id = get_queried_object_id();
	if (! $id) {
		return $title . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing(id empty)  wpseo_title]' : '');
	}
	
	$title_replaced = get_the_title($id); 
	$title_replaced = str_replace( '&#8211;', '-', $title_replaced );
	$title_replaced = str_replace( ' [DEBUG: removed]', '', $title_replaced );
	
	if ( mb_strlen($title_replaced) < 3 ) {
		return $title . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing (too short)]' : '');
	}
	
	$start = -1;
	$end = 0;
	$idx1 = 0;
	$idx2 = 0;
	$matched = false;
	while ( $idx1 < mb_strlen($title) && $idx2 < mb_strlen($title_replaced) ) {
		$ch1 = mb_substr($title, $idx1, 1, "UTF-8");
		if ( $ch1 == ' ' ) {
			$idx1++;
			continue;
		}
		$ch2 = mb_substr($title_replaced, $idx2, 1, "UTF-8");
		if ( $ch2 == ' ' ) {
			$idx2++;
			continue;
		}
		if ( $ch1 != $ch2 ) {
			if (! $matched) {
				// none
			} else {
				$matched = false;
				$idx2 = 0;
				$start = -1;
				$end = 0;
			}
			$idx1++;
		} else {
			if (! $matched) {
				$matched = true;
				$start = $idx1;
				$end = $idx1;
			} else {
				$end = $idx1;
			}
			$idx1++;
			$idx2++;
		}
	}

	if ( $start < 0 || $idx2 < mb_strlen($title_replaced) ) {
		$title_new = kerorin23_yoastseo_adapter_remove_space($title, $id);
		$title_new = kerorin23_yoastseo_adapter_replace_space($title_new);
		
		return $title_new . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG:  (title not included)]' : '');
	} else {
		$title_new  = kerorin23_yoastseo_adapter_replace_space( kerorin23_yoastseo_adapter_remove_space(mb_substr($title, 0, $start, "UTF-8"), $id) );
		$title_new .= $title_replaced;
		$title_new .= kerorin23_yoastseo_adapter_replace_space( kerorin23_yoastseo_adapter_remove_space(mb_substr($title, $end+1, NULL, "UTF-8"), $id) );

		return $title_new . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: removed wpseo_title]' : '');
	}
}

function kerorin23_yoastseo_adapter_display_title($title, $id) {
	global $post;
	if (!is_single() && !is_page() && !is_preview() && !is_archive() && !is_search() && !is_home()) {
		return $title . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing]' : '');
	}
	$post->post_title=kerorin23_yoastseo_adapter_remove_space($title, $id);

	return $post->post_title;
}

function kerorin23_yoastseo_wp_title($title, $id) {
	if (!$id) {
		return $title . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing(title id empty)]' : '');
	}
	return kerorin23_yoastseo_adapter_remove_space($title, $id);
}

function kerorin23_yoastseo_adapter_wpseo_description( $text ) {
	$text_new = kerorin23_yoastseo_adapter_display_content( $text );
	$text_new = kerorin23_yoastseo_adapter_replace_space( $text_new );
	
	return $text_new . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: removed and replaced wpseo_description]' : '');
}

function kerorin23_yoastseo_adapter_display_content($content) {
	$id = (is_single() || is_page()) ? get_queried_object_id() : get_the_ID();

	if (! $id) {
		return $content . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing(id empty)]' : '');
	}

	return kerorin23_yoastseo_adapter_remove_space($content, $id);
}

function kerorin23_yoastseo_adapter_remove_space( $content, $id ) {
	if (get_post_meta($id, '_kerorin23_yoastseo_adapter_widget_enabled', true) != "1") {
		return $content . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: skip removing(disabled)]' : '');
	}

	$KERORIN23_YOASTSEO_ASCII_CHARS = ' !"#$%&' ."'". '()*+,-./0123456789:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';

	$new_content = '';

	$isascii = false;
	$prevascii = false;
	$prevspace = false;
	for($i=0; $i<mb_strlen($content); $i++) {
		$ch = mb_substr($content, $i, 1, "UTF-8");

		if ($ch == ' ') {
			// 半角スペースが２つ続いたら出力
			if ($prevspace) {
				$new_content .= ' ';
			}
			// 出力をペンディングして次へ
			$prevspace = true;
			continue;
		} else {
			// 文字が半角英数記号か判定
			if ($ch == "¥n" || 
			    $ch == "¥t" || 
			    $ch == "¥r" ||
			    (strpos($KERORIN23_YOASTSEO_ASCII_CHARS, $ch) >= 1)) {

				$isascii = true;
			} else {
				$isascii = false;
			}

			// 半角英数の間のスペースのみ出力
			if ($prevspace && $prevascii && $isascii) {
				$new_content .= ' ';
			}
			$prevspace = false;

			$new_content .= $ch;
			$prevascii = $isascii;

			continue;
		}
	}

	return $new_content  . ($GLOBALS['KERORIN23_YOASTSEO_ADAPTER_DEBUG'] ? ' [DEBUG: removed]' : '');
}

/** SEOタイトル、SEOデイスクリプションでは*を半角スペースに変換 */
function kerorin23_yoastseo_adapter_replace_space($text) {
	return str_replace('*', ' ', $text);
}
?>