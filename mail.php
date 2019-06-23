<?php header("Content-Type:text/html;charset=utf-8"); ?>
<?php
require_once('lib.php');
require_once('define.php');
require_once('db.php');
require_once('html.php');

$gdb = new GET_SET_DB;
$gdf = new SITE_EACH_DATA($gdb,$_POST['AntennaID']);
//-----------------必須設定　必ず設定してください。-------------

//サイトのトップページのURL　※送信完了後に「トップページへ戻る」ボタンが表示されますので
$site_top = $gdf->SITE_URL;

// 管理者メールアドレス ※メールを受け取るメールアドレス(複数指定する場合は「,」で区切ってください)
$to = $gdf->MAIL_ADDRESS;

//-----------------必須設定　ここまで--------------------------


//------------ 任意設定　以下は必要に応じて設定してください --------------

// このPHPファイルの名前 ※ファイル名を変更した場合は必ずここも変更してください。
$file_name ="mail.php";

// 管理者宛のメールで差出人を送信者のメールアドレスにする(する=1, しない=0)
// する場合は、メール入力欄のname属性の値を「Email」にしてください。例 <input size="30" type="text" name="Email" />
//メーラーなどで返信する場合に便利なので「する」がおすすめです。
$fromAdd = 1;

// 管理者宛に送信されるメールのタイトル（件名）
$sbj = $gdf->SITE_TITLE."登録依頼";

// 送信確認画面の表示(する=1, しない=0)
$confirmDsp = 0;

// 送信完了後に自動的に指定のページ(サンクスページなど)に移動する(する=1, しない=0)
// CV率を解析したい場合などはサンクスページを別途用意し、URLをこの下の項目で指定してください。
// 0にすると、デフォルトの送信完了画面が表示されます。
$jumpPage = 0;

// 送信完了後に表示するページURL（上記で1を設定した場合のみ）※httpから始まるURLで指定ください。
$thanksPage = "http://xxxxxxxxx.xx/thanks.html";

// 差出人に送信内容確認メール（自動返信メール）を送る(送る=1, 送らない=0)
// 送る場合は、メール入力欄のname属性の値を「Email」にしてください。例 <input size="30" type="text" name="Email" />
// また差出人に送るメール本文の文頭に「○○様」と表示さたい場合は名前入力欄のname属性を name="名前"としてください
$remail = 1;

// 差出人に送信確認メールを送る場合のメールのタイトル（上記で1を設定した場合のみ）
$resbj = "送信ありがとうございました";

//自動返信メールに署名を表示(する=1, しない=0)※管理者宛にも表示されます。
$mailFooterDsp = 0;

//上記で「1」を選択時に表示する署名（FOOTER～FOOTER;の間に記述してください）
$mailSignature = <<< FOOTER

──────────────────────
株式会社○○○○　佐藤太郎
〒150-XXXX 東京都○○区○○ 　○○ビル○F　
TEL：03- XXXX - XXXX 　FAX：03- XXXX - XXXX
携帯：090- XXXX - XXXX 　
E-mail:xxxx@xxxx.com
URL: http://www.kens-web.com/
──────────────────────

FOOTER;

// 必須入力項目を設定する(する=1, しない=0)
$esse = 1;

/* 必須入力項目(入力フォームで指定したname属性の値を指定してください。（上記で1を設定した場合のみ）
値はシングルクォーテーションで囲んで下さい。複数指定する場合は「,」で区切ってください)*/
$eles = array('RSSURL','Email','URL','SPAMKILL','申請内容','AntennaID');

//--------------------- 任意設定ここまで -----------------------------------

// 以下の変更は知識のある方のみ自己責任でお願いします。

$sendmail = 0;
foreach($_POST as $key=>$val) {
	if($val == "submit") $sendmail = 1;
}
// 文字の置き換え
$string_from = "＼";
$string_to = "ー";
// 未入力項目のチェック
if($esse == 1) {
	// SPAMチェック
	if($_POST['SPAMKILL'] !== '1234'){
	echo('SPAM認定されました。正しくSPAMチェックを入力してください。');
		exit;
	}
	if($_POST['URL'] === $_POST['RSSURL']){
	echo('SPAM認定されました。URLとRSSに同じものが入力されています。');
		exit;
	}

	$empty_flag = 0;
	$length = count($eles) - 1;
	foreach($_POST as $key=>$val) {
		$key = strtr($key, $string_from, $string_to);
		if($val == "submit") ;
		else {
			for($i=0; $i<=$length; $i++) {
				if($key == $eles[$i] && empty($val)) {
					$errm .= "<FONT color=#ff0000>「".$key."」は必須入力項目です。</FONT><br>\n";
					$empty_flag = 1;
				}
			}
		}
	}
	foreach($_POST as $key=>$val) {
		$key = strtr($key, $string_from, $string_to);
		for($i=0; $i<=$length; $i++) {
			if($key == $eles[$i]) {
				$eles[$i] = "check_ok";
			}
		}
	}
	for($i=0; $i<=$length; $i++) {
		if($eles[$i] != "check_ok") {
			$errm .= "<FONT color=#ff0000>「".$eles[$i]."」が未選択です。</FONT><br>\n";
			$eles[$i] = "check_ok";
			$empty_flag = 1;
		}
	}
}

///-----------入力管理用
if($_POST['SPAMKILL'] == '1234'){
	$file_path = $gdf->FOLDER_DB.'wait_list.csv';
	//ファイル作成
	if(touch($file_path)){
		//ファイルのパーティションの変更
		if(chmod($file_path,0644)){
		}else{
			//失敗
			exit;
		}
	}else{
		//失敗
		exit;
	}
}
$fp = fopen($file_path,'a') or die('ファイルが開けません');
flock($fp, LOCK_EX);
fwrite($fp, $_POST['SiteName']."\t".$_POST['URL']."\t".$_POST['RSSURL']."\t".$_POST['Category']."\n");
flock($fp, LOCK_UN);
fclose($fp);

///------------自動登録
/*if($_POST['SPAMKILL'] == '1234'){
	$file_path = '../link.csv';
	//ファイル作成
	if(touch($file_path)){
		//ファイルのパーティションの変更
		if(chmod($file_path,0644)){
		}else{
			//失敗
			exit;
		}
	}else{
		//失敗
		exit;
	}
}*/
$db_load = 0;
$db_each = 0;
$db_status = 0;
if($_POST['申請内容'] == '新規登録'){
	$db_load = 0;
	$db_each = 1;
	$db_status = 0;
}elseif($_POST['申請内容'] == '登録情報変更'){
	$db_load = 1;
	$db_each = 1;
	$db_status = 2;
}
/*$fp = fopen($file_path,'a') or die('ファイルが開けません');
flock($fp, LOCK_EX);
fwrite($fp, '111'."\t".$_POST['SiteName']."\t".preg_replace('/(\s|　)/','',$_POST['URL'])."\t".preg_replace('/(\s|　)/','',$_POST['RSSURL'])."\t".$_POST['Category']."\t".'1'."\t".'1'."\t"."\n");
flock($fp, LOCK_UN);
fclose($fp);
*/
$d_cate = $_POST['Category'];
if($d_cate === '選択してください' || $d_cate === ''){
	$d_cate = 'none';
}
//DB登録
$insert_data = array(
	sqlite_escape_string(htmlspecialchars($_POST['SiteName'], ENT_QUOTES)),
	htmlspecialchars(preg_replace('/(\s|　)/','',$_POST['URL']), ENT_QUOTES),
	htmlspecialchars(makeRandHash(8), ENT_QUOTES),
	htmlspecialchars(preg_replace('/(\s|　)/','',$_POST['RSSURL']), ENT_QUOTES),
	//htmlspecialchars($_POST['Category'], ENT_QUOTES),
	htmlspecialchars($d_cate, ENT_QUOTES),
	$db_load,$db_each,0,0,0,0,"none",
	htmlspecialchars($_POST['名前'], ENT_QUOTES),
	htmlspecialchars($_POST['Email'], ENT_QUOTES),
	htmlspecialchars($_POST['その他'], ENT_QUOTES),
	htmlspecialchars(makeRandHash(16), ENT_QUOTES),
	htmlspecialchars(makeRandHash(16), ENT_QUOTES),
	$db_status,
	$_POST['AntennaID']
	);
$gdb->set_FeedData_Single($gdf->FOLDER_DB.$gdf->DB_LINK,$gdf->TABLE_SITE,$insert_data);


//SPAMチェック値を配列から削除
unset($_POST['SPAMKILL']);

// 管理者宛に届くメールのレイアウトの編集
$body="「".$sbj."」からメールが届きました\n\n";
$body.="＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
foreach($_POST as $key=>$val) {
	$key = strtr($key, $string_from, $string_to);
	//※追記　チェックボックス（配列）の場合は以下の処理で複数の値を取得するように変更
	$out = '';
	if(is_array($val)){
	foreach($val as $item){
	$out .= $item . ',';
	}
	if(substr($out,strlen($out) - 1,1) == ',') {
	$out = substr($out, 0 ,strlen($out) - 1);
	}
 }else { $out = $val;} //チェックボックス（配列）追記ここまで
	if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
	if($out == "submit" or $key == "httpReferer") ;
	else $body.="【 ".$key." 】 ".$out."\n";
}
$body.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n";
$body.="送信された日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
$body.="送信者のIPアドレス：".$_SERVER["REMOTE_ADDR"]."\n";
$body.="送信者のホスト名：".getHostByAddr(getenv('REMOTE_ADDR'))."\n";
$body.="問い合わせのページURL：".$_POST['httpReferer']."\n";
if($mailFooterDsp == 1) $body.= $mailSignature;
//--- レイアウトの編集終了 --->
if($remail == 1) {
//--- 差出人への送信確認メールのレイアウト
if(isset($_POST['名前'])){ $rebody = "{$_POST['名前']} 様\n\n";}
if($_POST['申請内容'] == '新規登録'){
	$rebody.="お問い合わせありがとうございました。\n";
	$rebody.="新規追加を受付ました。確認を行いますので今しばらくお待ち下さい。\n\n";
	$rebody.="送信内容は以下になります。\n\n";
}else{
	$rebody.="お問い合わせありがとうございました。\n";
	$rebody.="早急にご返信致しますので今しばらくお待ちください。\n\n";
	$rebody.="送信内容は以下になります。\n\n";
}
$rebody.="＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
foreach($_POST as $key=>$val) {
	$key = strtr($key, $string_from, $string_to);
	//追記　チェックボックス（配列）の場合は以下の処理で複数の値を取得するように変更
	$out = '';
	if(is_array($val)){
	foreach($val as $item){
	$out .= $item . ',';
	}
	if(substr($out,strlen($out) - 1,1) == ',') {
	$out = substr($out, 0 ,strlen($out) - 1);
	}
}else { $out = $val; }//チェックボックス（配列）追記ここまで
	if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
	if($out == "submit" or $key == "httpReferer") ;
	else $rebody.="【 ".$key." 】 ".$out."\n";
}
$rebody.="\n＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\n\n";
$rebody.="送信日時：".date( "Y/m/d (D) H:i:s", time() )."\n";
if($mailFooterDsp == 1) $rebody.= $mailSignature;
$reto = $_POST['Email'];
$rebody=mb_convert_encoding($rebody,"JIS","utf-8");
$resbj="=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($resbj,"JIS","utf-8"))."?=";
$reheader="From: $to\nReply-To: ".$to."\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
}
$body=mb_convert_encoding($body,"JIS","utf-8");
$sbj="=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($sbj,"JIS","utf-8"))."?=";
if($fromAdd == 1) {
	$from = $_POST['Email'];
	$header="From: $from\nReply-To: ".$_POST['Email']."\nContent-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
} else {
	$header="Reply-To: ".$to."\nContent-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
}
if(($confirmDsp == 0 || $sendmail == 1) && $empty_flag != 1){
	mail($to,$sbj,$body,$header);
	if($remail == 1) { mail($reto,$resbj,$rebody,$reheader); }
}
else if($confirmDsp == 1){
/*　▼▼▼送信確認画面のレイアウト※編集可　オリジナルのデザインも適用可能▼▼▼　*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>お問い合わせ確認画面</title>
<style type="text/css">
body{
	color:#666;
	font-size:90%;
	line-height:120%;
}
table{
	width:98%;
	margin:0 auto;
	border-collapse:collapse;
}
td{
	border:1px solid #ccc;
	padding:5px;
}
td.l_Cel{
	width:15%;
}
</style>
</head>
<body>

<!-- ▲ Headerやその他コンテンツなど　※編集可 ▲-->

<!-- ▼************ 送信内容表示部　※編集は自己責任で ************ ▼-->
<?php if($empty_flag == 1){ ?>
<div align="center"><h3>入力エラー</h3><?php echo $errm; ?><br><br><input type="button" value=" 前画面に戻る " onClick="history.back()"></div>
<?php
		}else{
?>
<div align="center">以下の内容で間違いがなければ、「送信する」ボタンを押してください。</div><br><br>
<form action="<?php echo $file_name; ?>" method="POST">
<table>
<?php
foreach($_POST as $key=>$val) {
	$key = strtr($key, $string_from, $string_to);
	//※追記　チェックボックス（配列）の場合は以下の処理で複数の値を取得するように変更　HTML側のname属性の値にも[と]を追加する。
	$out = '';
	if(is_array($val)){
	foreach($val as $item){
	$out .= $item . ',';
	}
	if(substr($out,strlen($out) - 1,1) == ',') {
	$out = substr($out, 0 ,strlen($out) - 1);
	}
 }
	else { $out = $val; }//チェックボックス（配列）追記ここまで
	if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
	$out = htmlspecialchars($out);
	$out=nl2br($out);//※追記 改行コードを<br>タグに変換
	$key = htmlspecialchars($key);
	print("<tr><td class=\"l_Cel\">".$key."</td><td>".$out);
	$out=str_replace("<br />","",$out);//※追記 メール送信時には<br>タグを削除
?>
<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $out; ?>">
<?php
	print("</td></tr>\n");
}
?>
</table><br>
<div align="center"><input type="hidden" name="mail_set" value="submit">
<input type="hidden" name="httpReferer" value="<?php echo $_SERVER['HTTP_REFERER'] ;?>">
<input type="submit" value="　送信する　">
<input type="button" value="前画面に戻る" onClick="history.back()">
</div>
</form>
<?php } ?>
<!-- ▲ *********** 送信内容確認部　※編集は自己責任で ************ ▲-->

<!-- ▼ Footerその他コンテンツなど　※編集可 ▼-->
</body>
</html>
<?php
/* ▲▲▲送信確認画面のレイアウト　※オリジナルのデザインも適用可能▲▲▲　*/
}
if(($jumpPage == 0 && $sendmail == 1) || ($jumpPage == 0 && ($confirmDsp == 0 && $sendmail == 0))) {

/* ▼▼▼送信完了画面のレイアウト　編集可 ※送信完了後に指定のページに移動しない場合のみ表示▼▼▼　*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>お問い合わせ完了画面</title>
</head>
<body>
<div align="center">
<?php if($empty_flag == 1){ ?>
<h3>入力エラー</h3><?php echo $errm; ?><br><br><input type="button" value=" 前画面に戻る " onClick="history.back()">
<?php
	}else{
?>
送信ありがとうございました。<br>
送信は正常に完了しました。<br><br>
<a href="<?php echo $site_top ;?>">トップページへ戻る⇒</a>
</div>
<div style="text-align:center;margin-top:15px;"><a style="font-size:11px;color:#aaa;text-decoration:none" href="http://www.kens-web.com/" target="_blank">- Ken'sWeb -</a></div>
<!--	CV率を計測する場合ここにAnalyticsコードを貼り付け -->
</body>
</html>
<?php
/* ▲▲▲送信完了画面のレイアウト 編集可 ※送信完了後に指定のページに移動しない場合のみ表示▲▲▲　*/
	}
}
//完了時、指定のページに移動する設定の場合、指定ページヘリダイレクト
else if(($jumpPage == 1 && $sendmail == 1) || $confirmDsp == 0) {
	 if($empty_flag == 1){ ?>
<div align="center"><h3>入力エラー</h3><?php echo $errm; ?><br><br><input type="button" value=" 前画面に戻る " onClick="history.back()"></div>
<?php }else{ header("Location: ".$thanksPage); }
} ?>
