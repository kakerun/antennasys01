<?php
require_once('define.php');
require_once('db.php');
require_once('lib.php');
//ピックアップ生成
function pickup_create(GET_SET_DB $gdb,GET_CATEGORY $gct,SITE_EACH_DATA $gdf,GET_DATE_TIME $gdp)
{
//require_once('lib.php');
//require_once('define.php');
//require_once('db.php');
//DB
//$gdb = new GET_SET_DB;
//カテゴリー
//$gct = new GET_CATEGORY;

$feeds = array(
	//RSSフィードを追加
);

$sitedata = array();

//10分前を取得
//$today = date('Y-m-d H:i');
//$getday = date('Y-m-d H:i', strtotime('-10 minutes' . $today));

//サイトデータ取得
$sitedata = $gdb->get_SiteData($gdf->FOLDER_DB.$gdf->DB_LINK,$gdf->ANTENNA_ID);

$file_path = $gdp->get_Feed_Path();

$cnt = 0;
//今日と昨日を一緒にやっちゃう
print_debug($file_path.'を開きます');
$feeds = $gdb->get_FeedData($file_path,$gdf->TABLE_ARTICLE,99,$gdf->ANTENNA_ID);
$file_path = $gdp->get_Feed_Path(-1);
print_debug($file_path.'を開きます');
//昨日のDBが存在するか
if(file_exists($file_path)){
	$feeds += $gdb->get_FeedData($file_path,$gdf->TABLE_ARTICLE,99,$gdf->ANTENNA_ID);
}
/*for($i = 0;$i < 2;++$i){
	if($i == 1){
		$file_path = $directory_name_yesterday."/".$db_name_yesterday;
	}
	//記事データ取得
	$feeds = get_DB_FeedData($file_path,"article_data",99);
}*/

$pickup = array();
$cnt = 0;

//PickUp
foreach($sitedata as $item_site){
	foreach($feeds as $item_feed){
		if($item_site['sitehash'] == $item_feed['sitehash']){
			//サイト1つにつき1つ
			print_debug($item_feed['sitecate']);
			$pickup[$cnt] = $item_feed;
			//siteinout追加
			$pickup[$cnt]['siteincount'] = $item_site['incount'];
			$pickup[$cnt]['siteoutcount'] = $item_site['outcount'];
			//inoutの比重
			$pickup[$cnt]['insout'] = get_Reduction_Rate($item_site['incount'],$item_site['outcount']);
 			++$cnt;
			break;
		}
	}
}

//ソート
$siteincount = array();
foreach($pickup as $key => $row){
	//$siteincount[$key] = $row['siteincount'];
	$siteincount[$key] = $row['insout'];
}
array_multisort($siteincount,SORT_DESC,$pickup);
//最初から15個の要素を取得
$array_temp = array();
/*for($i=0;$i<30;$i++){
	$a_temp = array();
	$a_temp = array_slice($pickup , 9 * $i, 9);
	shuffle($a_temp);
	$array_temp += $a_temp;
}*/
$array_temp = array_slice($pickup , 0, 15);
shuffle($array_temp);
//残り
$array_list = array();
$array_list = array_slice($pickup , 15);
//ランダム
shuffle($array_list);
//結合
foreach($array_list as $item){
	array_push($array_temp,$item);
}
$pickup = array();
$pickup = $array_temp;

$ranking = array();
$cnt = 0;

//ランキング ソートするだけ
$clickcount = array();
foreach($feeds as $key => $row){
	$clickcount[$key] = $row['clickcount'];
}
array_multisort($clickcount,SORT_DESC,$feeds);

//書き込み
$table_create_flag = false;
//PICKUPDBパス
$file_path = $gdf->FOLDER_DB.$gdf->DB_PICKUP;
//DBが存在するかを調べる
if( file_exists( $file_path ) ){
	/*//テーブルの存在を確かめる
	if($gdb->chk_Table_Exist($db,$table) == 0){
		//無かったらテーブル作る
		if($table === $this->TABLE_SITE){
			$this->make_DB_Table($db,$table);
		}elseif($table === $this->TABLE_ANTENNA){
			$this->make_DB_Table($db,$table,2);
		}else{
			$this->make_DB_Table($db,$table,1);
		}
	}*/
}else{
	$table_create_flag = true;
}

//DB書込 アンテナID_TABLE名で作成
$gdb->set_FeedData($file_path,$gdf->ANTENNA_ID.'_'.$gdf->TABLE_PICKUP,$pickup,$table_create_flag);
$gdb->set_FeedData($file_path,$gdf->ANTENNA_ID.'_'.$gdf->TABLE_RANKING,$feeds,$table_create_flag);


}

//pickup_create();
?>
