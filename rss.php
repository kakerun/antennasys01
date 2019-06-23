<?php
require_once("./library/feedcreator.class.php");
require_once('lib.php');
require_once('define.php');
require_once('db.php');
require_once('html.php');

function rss_create(GET_SET_DB $gdb,GET_CATEGORY $gct,SITE_EACH_DATA $gdf,$data_get,$cate_get,$date_get,$all = 0)
{
//header('Content-type:text/html; charset=utf-8');
print_debug($data_get['articlename'].$data_get['timestamp']);
//カテゴリー
//$gct = new GET_CATEGORY;
//DB
//$gdb = new GET_SET_DB;

$category = $gct->c_rss();
$category_name = '総合';
$rss_index = 'index.xml';

$rss_h_switch = false;

$cate = $cate_get;

if($all == 99){
	//$cate = 99;
}elseif($all == 60){
	$rss_index = 'index_h.xml';
	//$cate_get = 99;
	$cate = 99;
	$rss_h_switch = true;
}else{
	//$cate =
	$rss_index = 'index_'.$cate.'.xml';
}

$feed_path = get_Feed_Path($date_get,$gdb->FOLDER_DB);
$feed_path_yesterday = get_Feed_Path(strtotime('-1 day' . $date_get),$gdb->FOLDER_DB);//前日分

//$cate = 99;
//if($all != 99){
//	$cate = $cate_get;
//	$category_name = $category[$cate];
//	$rss_index = 'index_'.$cate.'.xml';
//}


$rss = new UniversalFeedCreator();
$rss->useCached();
$rss->title = $gdf->SITE_TITLE;
$rss->description = $gdf->SITE_COMMENT;
$rss->link = $gdf->SITE_URL;
$rss->syndicationURL = $gdf->SITE_URL.$rss_index;

// get your news items from somewhere, e.g. your database:
//mysql_select_db($dbHost, $dbUser, $dbPass);
//$res = mysql_query("SELECT * FROM news ORDER BY newsdate DESC");

$feeds_rss = array();
$cnt = 0;
$feeds_rss[$cnt] = $data_get;
++$cnt;

if(file_exists($feed_path)){
	$feeds = $gdb->get_FeedData_RSS_All($feed_path,$gdf->TABLE_ARTICLE,$all);

	//前日のDBが存在するかどうか
	if(file_exists($feed_path_yesterday)){
		$feeds += $gdb->get_FeedData_RSS_All($feed_path_yesterday,$gdf->TABLE_ARTICLE,$all);
	}
	//ソート
	$timestamp = array();
	foreach($feeds as $key => $row){
		//$item[$key] = $row['item']->get_date('Y-m-d H:i');
		$timestamp[$key] = $row['timestamp'];
	}
	array_multisort($timestamp,SORT_DESC,$feeds);



	//選別
	foreach($feeds as $item){
		if($all == 0){
			if($cate == substr($item['rssnum'], -1, 2) && $item['rssnum'] != 0){
				$feeds_rss[$cnt] = $item;
				++$cnt;
			}
		}else{
			if($all >= $item['rssall'] && $item['rssall'] != 0){
				$feeds_rss[$cnt] = $item;
				++$cnt;
			}
		}
	}
}


$cnt = 0;
foreach($feeds_rss as $feed){
	//アンテナIDで揃え
	if($feed['antennaid'] !== $gdf->ANTENNA_ID){
		continue;
	}
	$item = new FeedItem();
	//$items = mb_strimwidth($field_array[4], 0, 80, "...",sjis);
	//$item->title = mb_strimwidth($items, 0, 60, "...",utf8);
	//RSSの""を排除
	if(substr($feed['articlename'], 0, 1) === '"' && substr($feed['articlename'], -1, 1) === '"'){
		$feed['articlename'] = str_replace('"', '', $feed['articlename']);
	}
	$item->title = $feed['articlename'];//mb_strimwidth($feed['articlename'], 0, 60, "...",utf8);
	$item->link = $gdf->SITE_RSSURL.$feed['rssnum'];
	$item->description = $feed['articlename']."など...";

	$date_conv = date('Y-m-d', strtotime($feed['timestamp']))."T".date('H:i', strtotime($feed['timestamp'])).":59".date('O');

	//$date_conv = "2011-01-02T03:04:05";
	//print $cnt." ".$item_name." ".$date_conv." ".$select_cachelist[$cnt]." ".$select_cachedate[$cnt]."<br />";
	$item->date = $date_conv;
	$item->source = $gdf->SITE_URL;
	$item->author = $gdf->SITE_AUTHER;

	$rss->addItem($item);

	++$cnt;
	if($cnt > 5){
		break;
	}
}

//ツイートします
//if($cate == 99){
	//一時的にツイートしなくしています
if($all == 999){
	require_once('twitter.php');
	//if(($minutes < 10) || ($minutes >= 20 && $minutes < 40) || ($minutes >= 50)){
	if(date('i') < 60){
		//tweet_create($item->title." ".$item->link);
		tweet_create($feeds_rss[0]['articlename']." ".$gdf->SITE_RSSURL.$feeds_rss[0]['rssnum']);
	}else{
		$array_tweet_list = array();
		$tf = fopen('./twitter_vocabulary.dat','r');
		if (flock($tf, LOCK_SH)){
			while (!feof($tf)) {
				array_push($array_tweet_list,fgets($tf));
			}
			flock($tf, LOCK_UN);
			$tweet_cnt = rand(0,count($array_tweet_list)-1);
			tweet_create($array_tweet_list[$tweet_cnt]);
		}else{
			 //print('ファイルロックに失敗しました');
		}
		fclose($tf);
	}
}
print_debug("生成してます");
//RSS更新
$rss->saveFeed("RSS1.0", './antenna/'.$gdf->ANTENNA_ID.'/rss/'.$rss_index, false);


}


//エラーログ
function error_logger_ins($target){
	//header('Content-type:text/html; charset=utf-8');
	$fh = fopen($gdf->FOLDER_LOG.'error.log','a');
	fwrite($fh, $target."\n");
	fclose($fh);
}
?>
