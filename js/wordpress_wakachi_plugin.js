/** word press 分かち書きプラグインJavascript */
var count=0;
// 分かち対象を指定 クリックイベント
jQuery('#wordpress_wakachi_widget_select_open').on( "click", function(){
	jQuery('#wordpress_wakachi_widget_select').show();
	jQuery(this).hide();
	jQuery('#wordpress_wakachi_widget_select_open_desc').show();
	// メッセージ欄位置調整
	jQuery('#wordpress_wakachi_widget_div_msg').css("top", "20%");
	return false;
});

// 分かち対象を指定を隠す クリックイベント
jQuery('#wordpress_wakachi_widget_select_close').on( "click", function(){
	jQuery('#wordpress_wakachi_widget_select').hide();
	jQuery('#wordpress_wakachi_widget_select_open_desc').hide();
	jQuery('#wordpress_wakachi_widget_select_open').show();
	// メッセージ欄位置調整
	jQuery('#wordpress_wakachi_widget_div_msg').css("top", "30%");
	return false;
});

// ビジーフラグ.
var wordpress_wakachi_widget_busy = false;
// ボタン活性制御
function wordpress_wakachi_widget_btn_enabled ( enabled ) {
	if ( !! enabled ) {
		jQuery( '#wordpress_wakachi_widget_btn_wakachi' ).removeAttr( 'disabled' );
		jQuery( '#wordpress_wakachi_widget_btn_join' ).removeAttr( 'disabled' );
	} else {
		jQuery( '#wordpress_wakachi_widget_btn_wakachi' ).attr( 'disabled', "disabled" );
		jQuery( '#wordpress_wakachi_widget_btn_join' ).attr( 'disabled', "disabled" );
	}
} 

// 分かち書きボタン クリックイベント
jQuery('#wordpress_wakachi_widget_btn_wakachi').on( "click", function(){
	if (wordpress_wakachi_widget_busy) {
		return false;
	}
	wordpress_wakachi_widget_busy = true;
	wordpress_wakachi_widget_btn_enabled( false );
	
	if (! jQuery('#wordpress_wakachi_widget_yahoo_appid').html() || jQuery('#wordpress_wakachi_widget_yahoo_appid').html() == "") {
		alert("YahooアプリケーションIDが設定されていません。\n\n[設定] - [分かち書きプラグイン設定] 画面で\nYahooアプリケーションIDを入力してください。");
		wordpress_wakachi_widget_btn_enabled( true );
		wordpress_wakachi_widget_busy = false;
		return false;
	}
	
	wordpress_wakachi_widget_wakachi();
	// フラグの解除はajaxコールバックで行う.
	return false;
});

// 結合ボタン クリックイベント
jQuery('#wordpress_wakachi_widget_btn_join').on( "click", function(){
	if (wordpress_wakachi_widget_busy) {
		return false;
	}
	wordpress_wakachi_widget_busy = true;
	wordpress_wakachi_widget_btn_enabled( false );
	
	try {
		wordpress_wakachi_widget_join();
	} finally {
		setTimeout( function(){ wordpress_wakachi_widget_btn_enabled( true ); wordpress_wakachi_widget_busy = false; }, 1000 );
	}
	return false;
});

// 結合用ASCII判定
var WORDPRESS_WAKACHI_WIDGET_ASCII_CHARS = "!\"#$%&'()*+,-./0123456789:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
// 結合処理
function wordpress_wakachi_widget_join() {
	var domMsg = jQuery('#wordpress_wakachi_widget_msg');
	domMsg.html( "結合中...<br />何も操作しないでください" );
	domMsg.css( "color", "#999999" );
	domMsg.show();
		
	var focuskw = jQuery('#focus-keyword-input-metabox').val();
	
	var executed = false;
	
	if ( jQuery('#wordpress_wakachi_enable_title').is(":checked") ) {
		var domTitle = jQuery('#title');
		var gudenTitle=jQuery('.editor-post-title__input');
		if(domTitle.length){
			if (!!domTitle) {
				domTitle.val( wordpress_wakachi_widget_join_impl(domTitle.val(), focuskw) );
				executed = true;
			}
		}else if(gudenTitle.length){
			if (!!gudenTitle) {
				wp.data.dispatch( 'core/editor' ).editPost( { title: wordpress_wakachi_widget_join_impl(gudenTitle.val(), focuskw) } );
				
				executed = true;
			}
		}
		
		
	}
	if ( jQuery('#wordpress_wakachi_enable_content').is(":checked") ) {
		var domTitle = jQuery('#title');
		var gudenTitle=jQuery('.editor-post-title__input');
		if(domTitle.length){
			var domContent = jQuery( '#content' );
			var domVisualCompBtn = jQuery( '.wpb_switch-to-composer' );
			
			// ビジュアルコンポーザープラグイン環境の判定
			var isVisualComp = jQuery( '#wpb_visual_composer' ).is(":visible");
			if ( isVisualComp ) {
				domVisualCompBtn.click();
			}
			// テキストモード判定
			var isVisible = domContent.is(":visible");
			if (! isVisible) {
				jQuery('#content-html').click();
			}
			
			domContent.val( wordpress_wakachi_widget_join_impl(domContent.val(), focuskw) );
			
			if (! isVisible) {
				jQuery('#content-tmce').click();
			}
			if ( isVisualComp ) {
				domVisualCompBtn.click();
			}		
			executed = true;
		}else if(gudenTitle.length){
			
			var sideBar=jQuery('.components-button.components-dropdown-menu__toggle.has-icon');
			sideBar.click();

			var domVisualBtn = jQuery('button:contains("ビジュアルエディター")');
// 			var domCodeBtn = jQuery('span:contains("コードエディター")').parent();
			var visualBtn=domVisualBtn.attr("aria-checked");
// 			var codeBtn=domCodeBtn.attr("aria-checked");
			if(visualBtn==="true"){
				var visualContent = jQuery( '.block-editor-block-list__layout.is-root-container');
				visualContent.html( wordpress_wakachi_widget_join_impl(visualContent.html(), focuskw) );
			}
			else {
			var domContent = jQuery( '.editor-post-text-editor' );
			domContent.val( wordpress_wakachi_widget_join_impl(domContent.val(), focuskw) );
			}

			sideBar.click();
				
			executed = true;
		}
		
	}
	if ( jQuery('#wordpress_wakachi_enable_seotitle').is(":checked") ) {
		var domSeoTitle = jQuery('#yoast-google-preview-title-metabox span').last();
		if (!! domSeoTitle) {
			setTimeout(function(){domSeoTitle.text( wordpress_wakachi_widget_join_impl(domSeoTitle.text(), focuskw) ); }, 300);
			
			executed = true;
		}
	}
	if ( jQuery('#wordpress_wakachi_enable_seodesc').is(":checked") ) {
		var domSeoDesc = jQuery('#yoast-google-preview-description-metabox span').last();
		if (!! domSeoDesc) {
			setTimeout(function(){domSeoDesc.text( wordpress_wakachi_widget_join_impl(domSeoDesc.text(), focuskw) ); }, 300);
			
			executed = true;
		}
	}
	
	if ( executed ) {
		domMsg.html( "結合完了！" );
		setTimeout( function(){ jQuery('#wordpress_wakachi_widget_msg').css( "color", "#CCCCCC"); }, 900 );
		setTimeout( function(){ jQuery('#wordpress_wakachi_widget_msg').hide(100); }, 1000 );
	} else {
		setTimeout( function(){ jQuery('#wordpress_wakachi_widget_msg').hide(100); }, 500 );
	}
}

/**
 * テキストを１文字づつ分解.
 * @param text String 元文書
 * @param array of String
 */
function wordpress_wakachi_widget_text_to_array( text ) {
	return text.match(/[\uD800-\uDBFF][\uDC00-\uDFFF]|[^\uD800-\uDFFF]/g) || [];
}

function wordpress_wakachi_widget_join_impl( text, focuskw ) {
	// フォーカスキーワードの空白を残すため、一時的に置換(先頭と末尾がasciiかどうかで置換文字列を変える)
	var replaced = "";
	if ( !! focuskw ) {
		var arrKwChars = wordpress_wakachi_widget_text_to_array( focuskw );
		if ( WORDPRESS_WAKACHI_WIDGET_ASCII_CHARS.indexOf(arrKwChars[0]) < 0 ) {
			replaced += "あ";
		}
		replaced +=  "WAKACHIxPLUGINxSEOxxFOCUSKEYWORD";
		if ( WORDPRESS_WAKACHI_WIDGET_ASCII_CHARS.indexOf(arrKwChars[arrKwChars.length -1]) < 0 ) {
			replaced += "あ";
		}		
		text = text.replace( focuskw, replaced );
	}

	var arrChars = wordpress_wakachi_widget_text_to_array( text );
	var new_text = "";
	
	var isascii = false;
	var prevascii = false;
	var prevspace = false;

	for (var i = 0; i < arrChars.length; i ++) {
		var ch = arrChars[i];
		if (ch == ' ') {
			if (prevspace) {
				new_text += ' ';
			}
			prevspace = true;
			continue;
		} else {
			if ( ch =="¥n" || 
			     ch == "¥t" || 
			     ch == "¥r" || 
			     (WORDPRESS_WAKACHI_WIDGET_ASCII_CHARS.indexOf(ch) >= 0) ) {
			     	isascii = true;
			} else {
			     	isascii = false;
			}
			
			if ( prevspace && prevascii && isascii) {
				new_text += ' ';
			}
			prevspace = false;
			
			new_text += ch;
			prevascii = isascii;			
			continue;
		}
	}
	
	if ( !! focuskw ) {
		new_text = new_text.replace( replaced, focuskw );
	}
	
	return new_text;
}

// ajax実行数カウント
var wordpress_wakachi_widget_wakachi_executed = 0;
// ajax実行エラーフラグ.
var wordpress_wakachi_widget_wakachi_error_flg = false;

function wordpress_wakachi_widget_wakachi() {
	wordpress_wakachi_widget_wakachi_error_flg = false;
	wordpress_wakachi_widget_wakachi_executed = 0;
	
	var domMsg = jQuery('#wordpress_wakachi_widget_msg');
	domMsg.html( "処理中...<br />何も操作しないでください" );
	domMsg.css( "color", "#999999" );
	domMsg.show();
	
	var focuskw = jQuery('#focus-keyword-input-metabox').val();

	if ( jQuery('#wordpress_wakachi_enable_title').is(":checked") ) {
		var domTitle = jQuery('#title');count=0;
        var gudenTitle=jQuery('.editor-post-title__input');
		if(domTitle.length){
			if (!!domTitle) {
				wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_title, domTitle.val(), focuskw );
				wordpress_wakachi_widget_wakachi_executed++;count=1;
			}
		}
		else if(gudenTitle.length){
			if (!!gudenTitle) {
				wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_title, gudenTitle.val(), focuskw );
				wordpress_wakachi_widget_wakachi_executed++;count=1;
			}
		}
		
	}
	if ( jQuery('#wordpress_wakachi_enable_content').is(":checked") ) {
		var domTitle = jQuery('#title');
		var gudenTitle=jQuery('.editor-post-title__input');
		if(domTitle.length){

			var domContent = jQuery('#content');
			var domVisualCompBtn = jQuery( '.wpb_switch-to-composer' );
			
			// 編集環境の判定
			var isVisualComp = jQuery( '#wpb_visual_composer' ).is(":visible");
			var isVisible = domContent.is(":visible");

			if ( isVisualComp ) {
				jQuery( '.wpb_switch-to-composer' ).click();
			}
			if (! isVisible) {
				jQuery('#content-html').click();
			}
			wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_content, domContent.val(), focuskw );
			wordpress_wakachi_widget_wakachi_executed++;
			if (! isVisible) {
				jQuery('#content-tmce').click();
			}
			if ( isVisualComp ) {
				jQuery( '.wpb_switch-to-composer' ).click();
			}
		}else if(gudenTitle.length){
			var sideBar=jQuery('.components-button.components-dropdown-menu__toggle.has-icon');
			sideBar.click();

			var domVisualBtn = jQuery('button:contains("ビジュアルエディター")');
// 			var domCodeBtn = jQuery('button:contains("コードエディター")').length;
			
			var visualBtn=domVisualBtn.attr("aria-checked");
// 			var codeBtn=domCodeBtn.attr("aria-checked");
			
			if(visualBtn==="true"){
				var visualContent = jQuery( '.block-editor-block-list__layout.is-root-container' );
				wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_content, visualContent.html(), focuskw );
				wordpress_wakachi_widget_wakachi_executed++;
			}
			if (visualBtn==="false") {
				var domContent = jQuery( '.editor-post-text-editor' );
				wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_content, domContent.val(), focuskw );
				wordpress_wakachi_widget_wakachi_executed++;
			}

			sideBar.click();
				
		}
	}
	if ( jQuery('#wordpress_wakachi_enable_seotitle').is(":checked") ) {
		var domSeoTitle = jQuery('#yoast-google-preview-title-metabox span').last();
		if (!! domSeoTitle) {
				setTimeout(function(){ wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_seotitle, domSeoTitle.text(), focuskw ); }, 700);
			
			wordpress_wakachi_widget_wakachi_executed++;
		}
			
	}
	if ( jQuery('#wordpress_wakachi_enable_seodesc').is(":checked") ) {
		var domSeoDesc = jQuery('#yoast-google-preview-description-metabox').last();
		if (!! domSeoDesc) {
			setTimeout(function(){wordpress_wakachi_widget_wakachi_impl( wordpress_wakachi_widget_wakachi_seodesc, domSeoDesc.text(), focuskw); }, 700);
			
			wordpress_wakachi_widget_wakachi_executed++;
		}
	}
	
	if ( wordpress_wakachi_widget_wakachi_executed == 0 ) {
		setTimeout( function(){ jQuery('#wordpress_wakachi_widget_msg').hide(); }, 500 );
		setTimeout( function(){ wordpress_wakachi_widget_btn_enabled( true ); wordpress_wakachi_widget_busy = false; }, 1000 );
	}
	// 実行した場合はajaxコールバックでメッセージを消す
}

function wordpress_wakachi_widget_wakachi_impl( callback, text, focuskw ) {
	var data = "appid=" + jQuery('#wordpress_wakachi_widget_yahoo_appid').html();
	data += "&licensekey=" +jQuery('#wordpress_wakachi_widget_license_key').html();
	data += "&siteurl=" +jQuery('#wordpress_wakachi_widget_site_url').html();
	data += "&sentence=" + encodeURIComponent(text);
	if ( !! focuskw ) {
		data += "&focuskw=" + encodeURIComponent(focuskw);
	}
	
	var param = {
		"type" : "POST",
		"url" : jQuery('#wordpress_wakachi_widget_ajax_url').html(),
		"cache" : false,
		"dataType" : 'text',
		"error" : wordpress_wakachi_widget_wakachi_error,
		"success" : callback,
		"complete" : wordpress_wakachi_widget_wakachi_complete,
		"data" : data
	};
	
 	jQuery.ajax( param );
}

/**
 * ajax NGコールバック
 * @param req XMLHttpRequest
 * @param status textStatus
 * @param e errorThrown
 */
function wordpress_wakachi_widget_wakachi_error( req, status, e ) {
	var msg;
	if (req.status == 501) {
		msg = "Yahoo日本語解析サービスの応答がエラーになりました。\n" + req.status + " " + status + " " + e +"\n" + "(YahooアプリケーションIDが入力済みかどうか確認してください)";
	} else if (req.status == 503) {
		msg = "Yahoo日本語解析サービスの応答がエラーになりました。\n" + req.status + " " + status + " " + e +"\n" + "(YahooアプリケーションIDが有効かどうか確認してください)";
	} else {
		msg = "サーバー通信がエラーになりました。\n時間をおいてやり直すか、開発元へお問い合わせください。\n" + req.status + " " + status + "\n" + e;
	}
	wordpress_wakachi_widget_wakachi_error_flg = true;
	alert(msg);	
}
/**
 * ajax 完了コールバック
 * @param req XMLHttpRequest
 * @param status textStatus
 */
function wordpress_wakachi_widget_wakachi_complete(req, status) {
	wordpress_wakachi_widget_wakachi_executed--;
	
	if ( wordpress_wakachi_widget_wakachi_executed == 0 ) {
		if (wordpress_wakachi_widget_wakachi_error_flg) {
			jQuery('#wordpress_wakachi_widget_msg').html( "処理終了" );
		} else {
			jQuery('#wordpress_wakachi_widget_msg').html( "分かち完了" );
		}
		setTimeout( function(){ jQuery('#wordpress_wakachi_widget_msg').css( "color", "#CCCCCC"); }, 900 );
		setTimeout( function(){ jQuery('#wordpress_wakachi_widget_msg').hide(100); }, 1000 );
		setTimeout( function(){ wordpress_wakachi_widget_btn_enabled( true ); wordpress_wakachi_widget_busy = false; }, 1000 );
	}
}

function wordpress_wakachi_widget_wakachi_title( data, dataType ) {
		data = data.substring(1);
		var domTitle = jQuery('#title');
		var gudenTitle=jQuery('.editor-post-title__input');
		if(domTitle.length){
			jQuery('#title').val(data);
		}else if(gudenTitle.length){
			wp.data.dispatch( 'core/editor' ).editPost( { title: data} );
			
// 			gudenTitle.val(data);
		}
}
function wordpress_wakachi_widget_wakachi_content( data, dataType ) {
		data = data.substring(1);
		var domTitle = jQuery('#title');
		var gudenTitle=jQuery('.editor-post-title__input');
		if(domTitle.length){
			var domContent = jQuery('#content');
			
			// 編集環境の判定
			var isVisualComp = jQuery( '#wpb_visual_composer' ).is(":visible");
			var isVisible = domContent.is(":visible");

			if ( isVisualComp ) {
				jQuery( '.wpb_switch-to-composer' ).click(); 
			}
			if (! isVisible) {
				jQuery('#content-html').click();
			}
			domContent.val( data );
			if (! isVisible) {
				jQuery('#content-tmce').click();
			}
			if ( isVisualComp ) {
				jQuery( '.wpb_switch-to-composer' ).click();
			}
		}else  if(gudenTitle.length){
			
			var sideBar=jQuery('.components-button.components-dropdown-menu__toggle.has-icon');
			sideBar.click();

			var domVisualBtn = jQuery('button:contains("ビジュアルエディター")');
// 			var domCodeBtn = jQuery('span:contains("コードエディター")').parent();
			var visualBtn=domVisualBtn.attr("aria-checked");
// 			var codeBtn=domCodeBtn.attr("aria-checked");
			
			
			if(visualBtn==="true"){
				var visualContent = jQuery('.block-editor-block-list__layout.is-root-container');
				visualContent.html( data );
			}
			if(visualBtn==="false"){
				var domContent = jQuery('.editor-post-text-editor');
				domContent.val( data );
			}
		
			sideBar.click();
		}
}
function wordpress_wakachi_widget_wakachi_seotitle( data, dataType ) {
	data = data.substring(1);
	jQuery('#yoast-google-preview-title-metabox span').last().text(data); 
	
}
function wordpress_wakachi_widget_wakachi_seodesc( data, dataType ) {
	data = data.substring(1);
	jQuery('#yoast-google-preview-description-metabox span').last().text( data );
	
}
