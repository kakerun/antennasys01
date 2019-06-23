<?php
require_once('../../lib.php');
require_once('../../define.php');
require_once('../../db.php');
require_once('../../html.php');

//--------Class設定
//DB
$gdb = new GET_SET_DB;
//HTML生成
$mht = new MAKE_HTML;
//時間パス関係
$gdp = new GET_DATE_TIME($gdf->FOLDER_DB);

//ユーザーエージェント判別
$ua=$_SERVER['HTTP_USER_AGENT'];
$ua_smartphone = false;
if((strpos($ua,'iPhone')!==false)||(strpos($ua,'iPod')!==false)||(strpos($ua,'Android')!==false)) {
	$ua_smartphone = true;
}

// 値受け取り
//カテゴリー_0～30　サイト情報_info 登録サイト一覧_list トップ_99

//ページ番号
if(isset($_GET['p'])) {
	$page = $_GET['p'];
	if($page == 0){
		$page = 1;
	}
	//print("$name<br>\n");
}else{
	$page = 1;
}
//最大表示記事数
if(isset($_GET['m'])) {
	$max = $_GET['m'];
	//print("$id<br>\n");
}else{
	$max = $gdf->LIST_MAX;//表示数
	if($ua_smartphone == true){
		$max = $gdf->LIST_MAX;
	}
}
//日付
if(isset($_GET['d'])) {
	$date = $_GET['d'];
	$gdp->set_Date($date);
	//print("$id<br>\n");
}else{
	//$date = date('Ymd');//日付
}

//RSS
if(isset($_GET['t'])) {
	$tline = $_GET['t'];
	//print("$id<br>\n");
}else{
	$tline = 0;
}

//短縮RSSを分解
if($tline != 0){
	$date = "20".substr($tline, 0, 6);    // "f" を返す
}

//SEO対策
//if(isset($_GET['category'])) {
//  $cate_seo = $_GET['category'];
  //print("$id<br>\n");
//}else{
//  $cate_seo = 999;
//}

if(isset($_SERVER['ORIG_PATH_INFO']) ? 'ORIG_PATH_INFO' : 'PATH_INFO'){
	$pathinfo_index = isset($_SERVER['ORIG_PATH_INFO']) ? 'ORIG_PATH_INFO' : 'PATH_INFO';
	$pathinfo_index = substr($_SERVER[$pathinfo_index] , 1);
	//print $pathinfo_index;
	$uri_array = explode("/",$pathinfo_index);
	$uri = $uri_array[0];

	if(count($uri_array) > 1){
		//空なら削除
		if($uri_array[count($uri_array)-1] === ""){
			unset($uri_array[count($uri_array)-1]);
		}
	}
	//var_dump($uri_array);

	if($uri === ""){

	}elseif($uri === 'list' || $uri === 'info' || $uri === 'signup' || $uri === 'rank'){
		//固定メニュー
		$cate = $uri;
	}elseif(strlen($uri) <= 2){
		//カテゴリー
		$cate = $uri;
	}elseif(strlen($uri) == 8){
		//日付
		$date = $uri;
		$gdp->set_Date($date);
		//配列多かったら
		if(count($uri_array) == 2){
			$page = $uri_array[1];
		}elseif(count($uri_array) == 3){
			$cate = $uri_array[1];
			$page = $uri_array[2];
		}
	}elseif(strlen($uri) == 12){
		//RSS
		$tline = $uri;
		$date = "20".substr($tline, 0, 6);
	}
}else{
}


$category = $gct->c_data();
$category_name = $category[$cate];

//--------------ここからフィード

//フィード
$feeds = array();
//ランキングtop10
$rank = array();
//PickUp
$pickup = array();

//RSS
$rss_title = '';

//10分前を取得
//$now_time = date('Hi');
//時間の境目の場合
//if($now_time < 11 && $date == date('Ymd')){
//	$date = date('Ymd', strtotime('-10 minutes' . $date));
//}

//記事Path
$feed_path = $gdp->get_Feed_Path();
$feed_path_yesterday = $gdp->get_Feed_Path(-1);//前日分

//h2タイトル
$htitle = "";
//メタタグ名
$category_meta_name = '';

//目的別データ取得
switch($cate){
	//掲載サイト一覧
	case 'list':
		$htitle = "掲載サイト一覧";
		$category_meta_name = '掲載サイト一覧';
		//サイトデータ取得
		$feeds =  $gdb->get_SiteData($gdf->FOLDER_DB.$gdf->DB_LINK,$gdf->ANTENNA_ID);
		break;
	//サイト情報
	case 'info':
		$htitle = "サイト情報";
		$category_meta_name = 'このサイトについて';
		break;
	//登録依頼
	case 'signup':
		$htitle = "登録依頼";
		$category_meta_name = '登録依頼';
		break;
	//ランキング
	case 'rank':
		$htitle = "ランキング"." [24時間以内]";
		$category_meta_name = 'ランキング';
		//ランキング取得
		$rank = $gdb->get_FeedData($gdf->FOLDER_DB.$gdf->DB_PICKUP,$gdf->ANTENNA_ID.'_'.$gdf->TABLE_RANKING,99,$gdf->ANTENNA_ID,$max);
		break;
	//記事
	default:
		if($cate == 99){
			$htitle = "PickUp"." [24時間以内]";
			$category_meta_name = '';
		}else{
			$htitle = "$category[$cate] 記事一覧"." ".$gdp->str_date;
			$category_meta_name = $category_name;
		}

		//ピックアップ、ランキング取得
		$pickup = $gdb->get_FeedData($gdf->FOLDER_DB.$gdf->DB_PICKUP,$gdf->ANTENNA_ID.'_'.$gdf->TABLE_PICKUP,99,$gdf->ANTENNA_ID,$max);

		//データ数カウント
		$count = $gdb->get_count($feed_path,$gdf->TABLE_ARTICLE,$gct->r2c($cate),$gdf->ANTENNA_ID);
		//print $count;

		//フィードデータ取得
		//if($page == 1){
		//	$feeds = $gdb->get_FeedData($feed_path,"article_data",$gct->r2c($cate),$max);
		//}else{
			$feeds = $gdb->get_FeedData($feed_path,$gdf->TABLE_ARTICLE,$gct->r2c($cate),$gdf->ANTENNA_ID,$max,$max * ($page - 1));
		//}
		//記事MAX未満なら前日のも読み込む  //DBが存在するかどうか
		if($count < $max && file_exists($feed_path_yesterday)){
			//$feeds += array(
			array_push($feeds,array(
					'timestamp' => $gdp->get_Str_Date_Yesterday(),
					'sitename' => 0,
					'siteurl' => 0,
					'sitehash' => 'dayline',
					'articlename' => 0,
					'articleurl' => 0,
					'articlehash' => 0,
					'sitecate' => 0,
					'imgurl' => 0,
					'articlecontent' => 0,
					'clickcount' => 0,
					'rssnum' => 0,
					'rssall' => 0,
					'antennaid' => 0
					));
			$f_temp = $gdb->get_FeedData($feed_path_yesterday,$gdf->TABLE_ARTICLE,$gct->r2c($cate),$gdf->ANTENNA_ID,$max - $count,0);
			$feeds = array_merge($feeds,$f_temp);
		}

		//RSS滑りこませ
		if($tline != 0){
			$temp = array($gdb->get_FeedData_RSS($feed_path,$gdf->TABLE_ARTICLE,$tline,$gdf->ANTENNA_ID));
			$rss_title = $temp[0]['articlename'];
			//すでに取得フィードに存在してるかどうか
			$insert_flag = true;
			foreach($pickup as $item){
				if($item['rssnum'] == $tline){
					$insert_flag = false;
					break;
				}
			}
			if($insert_flag){
				//3番目に入れる
				array_splice($pickup,3,0,$temp);
			}
		}
		break;
}

//-----------------------------------------------------------------------------------------

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<?php
if($ua_smartphone == false){
}else{
	print "<meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0,minimum-scale=1.0\" />";
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo $gdf->SITE_MASTER_URL; ?>css/common.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $gdf->SITE_MASTER_URL; ?>css/index.css" />
<script type="text/javascript" src="<?php echo $gdf->SITE_MASTER_URL; ?>library/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="<?php echo $gdf->SITE_MASTER_URL; ?>library/scrolltopcontrol.js"></script>
<script type="text/javascript" src="<?php echo $gdf->SITE_MASTER_URL; ?>library/share-button.js"></script>
<meta name="author" content="<?php print $gdf->SITE_AUTHER;?>" />
<meta name="copyright" content="<?php print $gdf->SITE_AUTHER;?>" />
<meta name="keywords" content="<?php print $gdf->SITE_SEARCHWORD;?>" />
<?php
//検索除外用メタタグ
if($cate_seo != 999){
	//print "<meta name=\"robots\" rel=\"noindex\" />\n";
}
//コメント
print "<meta name=\"description\" content=\"";
if($rss_title === ''){
	print $gdf->SITE_COMMENT;
}else{
	print "PickUp記事:".$rss_title;
}
if($gdp->today_flg && $cate == 99 && $page == 1){
}elseif($gdp->today_flg){
	print "|".$category_meta_name."-page".$page;
}else{
	print $gdp->str_date."|".$category_meta_name."-page".$page;
}
print "\" />\n";
?>
<link rel="top" href="index.php" title="<?php print $gdf->SITE_TITLE;?>" />
<link rel="shortcut icon" href="<?php echo $gdf->SITE_MASTER_URL; ?>img/favicon.ico" />

<script type="text/javascript">
window.___gcfg = {lang: 'ja'};

(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

function wget(url)
{
	// 準備
	var http = null;
	if (window.XMLHttpRequest) {	// Safari, Firefox など
		http = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {	// IE
		try { http = new ActiveXObject("Msxml2.XMLHTTP"); }	// IE6
		catch (e) {
			try { http = new ActiveXObject("Microsoft.XMLHTTP"); }	// IE5
			catch (e) { return null; }	// Error
		}
	}

	// 同期通信
	http.open("GET", url, false);
	http.send(null);
	return http.responseText;
}

function ccnt(el,nuf,db)
{
	//var el = document.activeElement;
	//var url = el.href;
    //記事ハッシュ、サイトハッシュ、DB名
	wget("<?php print $gdf->SITE_MASTER_URL;?>gocount.php?cf=" + nuf + "&c=" + el + "&d=" + db);
}

function refcount()
{
	//逆アクセス
	url = document.referrer;
	murl = document.URL;
	aaa = wget("<?php print $gdf->SITE_MASTER_URL;?>file.php?url=" + url + "&murl=" + murl);
	alert(aaa + " " + url + " " + murl);
}

$(document).ready(function(){
    $('.accordion_head').click(function() {
        $(this).next().slideToggle();
    }).next().hide();
	$('.accordion_head').hover(function(){
		$(this).css("cursor","pointer");
	},function(){
		$(this).css("cursor","default");
	});
});
</script>

<?php
//タイトル
echo '<title>';
if($gdp->today_flg && $cate == 99 && $page == 1 && $rss_title === ''){
	print $gdf->SITE_TITLE.'|'.$gdf->SITE_SUBTITLE;
}elseif($rss_title != ''){
	print $rss_title.'|'.$gdf->SITE_TITLE.'|'.$gdp->str_date;
}elseif($gdp->today_flg){
	if($cate === 'info' || $cate === 'list' || $cate === 'signup' || $cate === 'rank'){
		print $gdf->SITE_TITLE.'|'.$category_meta_name;
	}else{
		print $gdf->SITE_TITLE.'|'.$category_meta_name."-page".$page;
	}
}else{
	print $gdf->SITE_TITLE.'|'.$gdp->str_date."|".$category_meta_name."-page".$page;
}
print "</title>\n";
?>

</head>
<body>
<!--アクセス解析-->

<!--ヘッダー-->
<div id="header">
<div id="title_index">
<span id="bookmark_item">

</span>
<a href="<?php print $gdf->SITE_URL;?>"><h1><?php print $gdf->SITE_TITLE;?></h1></a>
</div>

</div>

<div id="wrapper">

<?php
//メニュー
//項目noneなら表示なし
if(count($category) != 1){
	echo '<div id="nav">';
	echo '<h2 class="hidden">メニュー</h2>';
	//if($ua_smartphone){
		echo '<div class="accordion_head">MENU</div>';
	//}
	echo '<ul>';
	//メニューリスト
	$mht->menu_list($category,$cate,$gdf);
	echo '</ul>';
	echo '</div>';
}
?>

<div id="main">
<!--
<div id="top_pic">
</div>
-->

<div id="contents">

<?php
//PickUp画像
if($ua_smartphone == false){
	if($cate !== 'list' && $cate !== 'info' && $cate !== 'signup'){
		echo '<div class="top_cnt">';
		$mht->list_pickup_img($pickup,$gdf);
		echo '</div>';
	}
}
//広告 FRONT
$mht->dat_view($gdf->FOLDER_DAT.$gdf->DAT_AD_FRONT);

?>

<h2><?php echo $htitle ; ?></h2>

<?php
//メイン表示リスト
switch($cate){
	case 'list':
		print "<div id=\"hello\"><ul>";
		$mht->list_site($feeds);
		break;
	case 'info':
		print "<div id=\"hello\"><ul>";
		$mht->dat_view_ex($gdf->FOLDER_DAT.$gdf->DAT_INFO,$gdf);
		break;
	case 'signup':
		print "<div id=\"hello\"><ul>";
		$mht->dat_view_ex($gdf->FOLDER_DAT.$gdf->DAT_SIGNUP,$gdf);
		break;
	case 'rank':
		print "<div id=\"top_favo\"><ul>";
		$mht->list_rank($rank);
		break;
	case 99:
		print "<div id=\"top_favo\"><ul>";
		//PickUp
		$mht->list_pickup($pickup,$tline,$gdf);
		//普通の記事
		if($tline == 0){
			if($cate == 99){
				$htitle = "全体記事一覧"." ".$gdp->str_date;
			}else{
				$htitle = "$category[$cate] 記事一覧"." ".$gdp->str_date;
			}
			print "<li class=\"ft\"></li>";
			print "</ul></div>\n";

			print "<h2>$htitle</h2>\n";
			print "<div id=\"hello\">\n";
			print "<ul>\n";
			$mht->list_view($feeds);
		}else{

		}
		break;
	default:
		print "<div id=\"hello\"><ul>";
		$mht->list_view($feeds);
		break;
}

?>

<li class="ft">
<?php
if($cate !== "list" && $cate !== "info" && $cate !== "signup" && $cate !== "rank" && $tline == 0){
	//ナビ
	$mht->navi($gdf,$gdp->num_date,$page,$count,$max,$cate);
}
?>
</li>
</ul>
</div>

<?php
if($cate !== "list" && $cate !== "info" && $cate !== "signup" && $cate !== "rank"){
	echo '<div id="old_week">';
	//$date = date('Ymd');
	for($i = 0 ; $i >= -6 ; $i--){
		$date_yesterday = date('m/d', strtotime("$i day" . $gdp->getday));
		$link_yesterday = date('Ymd', strtotime("$i day" . $gdp->getday));
		if($link_yesterday <= $gdf->START_DATE){
			break;
		}
		echo "<a href=\"".$gdf->SITE_URL."index/".$link_yesterday."\">".$date_yesterday."</a>\n";
	}
	echo '</div>';
}
?>

</div>

<?php
//広告 BACK
$mht->dat_view($gdf->FOLDER_DAT.$gdf->DAT_AD_BACK);
?>

<!--サイドバー-->

<!--<div id="sidebar">-->

<?php
//逆アクセス観測
require_once($gdf->FOLDER_UP.'file.php');
reaccess($gdb->get_SiteData($gdf->FOLDER_DB.$gdf->DB_LINK,$gdf->ANTENNA_ID),$gdf->FOLDER_DB.$gdf->DB_LINK,$gdf,$gdb);
?>

<!--</div>--><!--サイドバー-->

</div><!--contents-->

</div><!--main-->

</div><!--wapper-->

<div id="footer">
<div id="footer_link">
<?php
//if($ua_smartphone == false){
	//echo "<ul>";
	//echo "<li>-RSS-</li>";
	//フッターRSS
	//$mht->footer_rss($gdf,$gct->c_rss());
	//echo "</ul>";
//}
//ソーシャルボタン
echo "<ul>";
echo $mht->sosial_button($gdf,"http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
echo "</ul>";
?>
<ul>
<li><a href="<?php echo $gdf->SITE_URL ?>index/info">このアンテナサイトについて</a></li>
<li><a href="<?php echo $gdf->SITE_URL ?>index/list">掲載サイト一覧</a></li>
<li><a href="<?php echo $gdf->SITE_URL ?>index/signup">登録フォーム</a></li>
</ul>
<ul>
</ul>
</div>
<div class="copyright">
copyright (C) 2010 <?php print $gdf->SITE_TITLE;?> all rights reserved
</div>
</div>

</body>
</html>
