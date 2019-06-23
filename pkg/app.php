<?php
//include('/home/kakerun/www/twobeko/cgi-bin/lunalys/analyzer/write.php');

//ユーザーエージェント判別
$ua=$_SERVER['HTTP_USER_AGENT'];
$ua_smartphone = false;
if((strpos($ua,'iPhone')!==false)||(strpos($ua,'iPod')!==false)||(strpos($ua,'Android')!==false)) {
	$ua_smartphone = true;
}

//if($ua_smartphone == false){
//	header('Location: http://twobeko.com/') ;
//}

require_once('./lib.php');
require_once('./autoloader.php');

// 値受け取り
//カテゴリー_0～30　サイト情報_info 登録サイト一覧_list トップ_99
//カテゴリーc 日d マックスm
if(isset($_GET['c'])) {
	$cate = $_GET['c'];
	//print("$id<br>\n");
}else{
	$cate = 99;  
}
if(isset($_GET['p'])) {
	$page = $_GET['p'];
	if($page == 0){
		$page = 1;
	}
	//print("$name<br>\n");
}else{
	$page = 1;
}
if(isset($_GET['m'])) {
	$max = $_GET['m'];
	//print("$id<br>\n");
}else{
	$max = 50;//表示数
	if($ua_smartphone == true){
		$max = 50;
	}
}
if(isset($_GET['d'])) {
	$date = $_GET['d'];
	//print("$id<br>\n");
}else{
	$date = date('Ymd');//日付
}
if(isset($_GET['r'])) {
	$rss = $_GET['r'];
	//print("$id<br>\n");
}else{
	$rss = 'none';
}
if(isset($_GET['n'])) {
	$newline = $_GET['n'];
	//print("$id<br>\n");
}else{
	$newline = 1;
}

//SEO対策
//if(isset($_GET['category'])) {
//  $cate_seo = $_GET['category'];
  //print("$id<br>\n");
//}else{
//  $cate_seo = 999;  
//}

$category  = category_data();
$category_name = $category[$cate];

//--------------ここからフィード

$feeds = array(
	//RSSフィードを追加
);
//フィード
$feeds_title = array();
$feeds_url = array();
$feeds_category = array();
$feeds_date = array();

$feeds_sitename = array();
$feeds_sitecate = array();
$feeds_countfile = array();
$feeds_duplicate = array();//重複有り判定

//RSS
$rss_light = 10000;
$rss_title = '';

//ページ
$page_now = $max * $page;
$cnt_pageitem = $max * ($page - 1);

$select_directory = "./db/".$date;
//$select_cachefile = date('Hi').".dat";
$select_cachelist = array();


//日を指定
if (file_exists($select_directory)){
}else{
	$select_directory = "./db/".date('Ymd', strtotime('-1 day' . $date));
}
$cnt = 0;

//次の日に0000.datがあるかどうか
$select_directory_tomorrow = "./db/".date('Ymd', strtotime('+1 day' . $date));
if(file_exists($select_directory_tomorrow."/0000.dat")){
	array_push($select_cachelist,$select_directory_tomorrow."/0000.dat");
	$cnt++;
}


//ファイル一覧を取得
if ($handle = opendir($select_directory)) {
	/* ディレクトリをループする際の正しい方法です */
	while (false !== ($file = readdir($handle))) {
		if($file != "." && $file != ".." && substr($file, 4, 4) === ".dat"){
			array_push($select_cachelist,$select_directory."/".$file);
			$cnt++;
		}
	}
	closedir($handle);
/*	//70ファイル未満なら前日のも読み込む
	if($cnt < 70){
		$cnt_limit = $cnt;
		//エラー回避用の1日前だったら
		if($select_directory === "./db/".date('Ymd', strtotime('-1 day' . $date))){
			$select_directory = "./db/".date('Ymd', strtotime('-2 day' . $date));
		}else{
			$select_directory = "./db/".date('Ymd', strtotime('-1 day' . $date));
		}
		if($handle = opendir($select_directory)) {
			$select_cachelist_yesterday = array();
			$cnt_wall = 0;
			while (false !== ($file = readdir($handle))) {
				if($file != "." && $file != ".." && substr($file, 4, 4) === ".dat"){
					array_push($select_cachelist_yesterday,$select_directory."/".$file);
					$cnt++;
				}
				//143ファイルで脱出
				if($cnt == 143){
					$cnt_wall = $cnt - $cnt_limit;
					//break;
				}
			}
			closedir($handle);
			//リバースして後ろから入れる
			rsort($select_cachelist_yesterday);
			for($i = 0;$i < $cnt_wall;$i++){
				array_push($select_cachelist,$select_cachelist_yesterday[$i]);
			}
		}
	}*/
	rsort($select_cachelist);
}

$cnt_pagelist = 1;
$cnt_page = 0;
$cnt = 0;

//最大アイテム数までループ
foreach ($select_cachelist as $item){
	$fp = fopen($item, 'r') or die('ファイルが開けません');
	
	if(substr($item, 14) === '0000.dat' && $cnt != 0){
		if($cate == 99 && $cnt > 50){
			fclose($fp);
			break;
		}elseif($cate != 99 && $cnt > 1){
			fclose($fp);
			break;
		}
		array_push($feeds_title, date('[Y年m月d日]', strtotime('-1 day' . $date)));
		array_push($feeds_url, '#');
		array_push($feeds_sitename, date('[Y年m月d日]', strtotime('-1 day' . $date)));
		array_push($feeds_category, 'dayline');
		array_push($feeds_date, 'dayline');
		array_push($feeds_countfile,'dayline');
		$cnt_pageitem++;
		$cnt++;
		$cnt_page++;
	}
	
	$cnt_newline = 1;
	//ファイル１行ずつ読み込み
	while ($field_array = fgetcsv($fp, 2048, "\t", ' ')) {
		
		//全部とカテゴリ別
		if($cate == 99 || $category_name === $field_array[6]){
			//ページの制限
			if($cnt < $cnt_pageitem){
			}elseif($cnt_pageitem <= $page_now - 1){
				array_push($feeds_title, str_replace("\"", "", $field_array[4]));
				array_push($feeds_url, $field_array[5]);
				array_push($feeds_sitename, str_replace("\"", "", $field_array[2]));
				array_push($feeds_category, $field_array[6]);
				array_push($feeds_date, $field_array[1]);
				array_push($feeds_countfile, substr($item, 5, 8).'-'.substr($item, 14, 4).'-'.$field_array[0]);

				$cnt_pageitem++;
				
			}else{
				//break;
			}
			
			$cnt++;
			$cnt_page++;
			
			//ページがたまったら増える
			if($cnt_page == $max){
				$cnt_pagelist++;
				$cnt_page = 0;
				//print $cnt_pagelist."<br />";
				//print $cnt_page."<br />";
			}
		}
		$cnt_newline++;
	}
	//ファイルを閉じる
	fclose($fp);
}

//while ($cnt_pageitem <= page_now) {
//}
//print $cnt_pagelist."<br />";
//print $cnt_page."<br />";
//print $cnt."<br />";
//-----------------------------------------------------------------------------------------

//if($page == 1){
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0" />
<link rel="stylesheet" type="text/css" href="css/sm_common.css" />
<link rel="stylesheet" type="text/css" href="css/sm_index.css" />
<script type="text/javascript" src="library/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="library/jquery.autopager-1.0.0.min.js"></script>
<script type="text/javascript" src="library/main.js"></script>
<meta name="author" content="poter" />
<meta name="copyright" content="2013 poter" />
<meta name="robots" rel="noindex" /><!-- 検索除外 -->
<link rel="top" href="index.php" title="2ベコあんてな" />
<link rel="shortcut icon" href="img/favicon.ico" />
<title>2ベコあんてな|スマホアプリ用</title>
</head>
<body>
<!--アクセス解析-->
<div id="tracker" style="position:absolute;visibility:hidden;">
<script type="text/javascript" src="http://www.twobeko.com/cgi-bin/lunalys/analyzer/tracker.js"></script>
<noscript><img src="http://www.twobeko.com/cgi-bin/lunalys/analyzer/write.php?guid=ON&act=img" width="0" height="0" alt="tracker"></noscript>
</div>
<div id="wrapper">
<div id="main">
		
<div id="contents">';
//カウンター
counter_co();
//表示はしない

//echo '<script type="text/javascript">
//$(function() {';
//echo "	$.autopager({autoLoad: true,content: '#helist'});";
//echo '});
//</script>';
echo "<div id=\"hello\">\n";
echo "<ul>\n";
echo "<div id=\"helist\">";
//}

//メイン表示リスト

app_view($feeds_title,$feeds_url,$feeds_sitename,$feeds_date,$feeds_countfile,$rss_light,$newline);


echo '</div>';
if($cnt_pagelist == $page){
	$date = date('Ymd', strtotime('-1 day' . $date));
	echo '<a href="./app.php?d='.$date.'&p=1" rel="next">続きを読み込み中</a>';
}else{
	++$page;
	echo '<a href="./app.php?d='.$date.'&p='.$page.'" rel="next">続きを読み込み中</a>';
}
//if($page == 2){
echo '
</ul>
</div><!-- hello end -->
</div><!-- content end -->
<!-- フッター削除 -->
</div><!-- main end -->
</div><!-- wapper end -->
</body>
</html>';
//}
?>