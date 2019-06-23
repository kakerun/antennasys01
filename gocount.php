<?php
header('Content-type:text/html; charset=utf-8');
require_once('lib.php');
require_once('define.php');
require_once('db.php');
require_once('html.php');

//サイトハッシュ
if(isset($_GET['c'])) {
  $site = $_GET['c'];
  //print("$id<br>\n");
}else{
  $site = 'none';
}

//記事ハッシュ
if(isset($_GET['cf'])) {
  $article = $_GET['cf'];
  //print("$id<br>\n");
}else{
  $article = 'none';
}

//DB名
if(isset($_GET['d'])) {
  $db_name = $_GET['d'];
  //print("$id<br>\n");
}else{
  $db_name = 'none';
}

if($site === 'none' || $article === 'none' || $db_name === 'none'){
	//print($site.' '.$article.' '.$db_name);
}else{
	//DB
	$gdb = new GET_SET_DB;
	$gdf = new SITE_FIX;

	$date = date('YmdHi');//今日の日付

	//$file_path = $gdf->FOLDER_LOG.$date.'.log';//ログファイルパス

	//DBフォルダ
	$directory_name = $gdf->FOLDER_DB.substr($db_name,0,6);
	//DBファイルパス
	$path = $directory_name."/".$db_name.'.db';

	//print($path);

	$feeddata = array();
	$sitedata = array();
	$cnt = 0;

	//記事DB書込
	$gdb->set_FeedData_Count_Out($path,$gdf->TABLE_ARTICLE,$article);
	//サイトDB書込
	$gdb->set_FeedData_Count_Out($gdf->FOLDER_DB.$gdf->DB_LINK,$gdf->TABLE_SITE,$site);

}

?>
