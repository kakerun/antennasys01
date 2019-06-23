<?php
//header('Content-type:text/html; charset=utf-8');

require_once('autoloader.php');
require_once('lib.php');
require_once('rss.php');
require_once('define.php');
require_once('db.php');
require_once('html.php');
require_once('pickup.php');

//function loader($date_time){
//--------Class設定
//DB
$gdb = new GET_SET_DB;
//DAT出力
$cdt = new CREATE_DAT;
//設定値
$gdfa = new SITE_FIX;
//時間パス関係
$gdp = new GET_DATE_TIME($gdfa->FOLDER_DB);

$feeds = array(
	//RSSフィードを追加
);

//アンテナ指定
if(isset($_GET['a'])){
	//アンテナID指定
	$antid = $_GET['a'];
	//print("$id<br>\n");
}else{
	die('アンテナIDが指定されていません');
}

//アンテナ取得
$ant_data = $gdb->get_AntennaData($gdfa->FOLDER_DB.$gdfa->DB_ANTENNA,$antid);
//$ant_all = $gdb->get_AntennaData_All($gdfa->FOLDER_DB.$gdfa->DB_ANTENNA);
//$gdp->set_Date_Time($date_time);

$feeds = $gdb->get_SiteData($gdfa->FOLDER_DB.$gdfa->DB_LINK,$antid);

//print "test<br />";
$first_items = array();
$cnt = 0;
$cnt_ft = 0;
foreach ($feeds as $url){
	if($url['articleload'] == 0){
		continue;
	}
	print_debug($url['sitename']." ".$url['sitehash']);
	//$item = $url[0];
    $feed = new SimplePie();
    $feed->set_feed_url($url['rssurl']);
    $feed->set_cache_duration(100);//キャッシュの保持時間
    $feed->enable_cache(false);
    $feed->init();
    $items_per_feed = 1;//フィード毎の取得数

	//print($url['rssurl'].'<br>');
    for ($x = 0; $x < $feed->get_item_quantity($items_per_feed); $x++){
		//アイテム
		$item = strtotime($feed->get_item($x)->get_date('Y/n/j G:i'));
		//入れる配列番号
		$num = $items_per_feed * $cnt + $x;
		//10分内かどうか
		if($item < strtotime($gdp->today) && $item >= strtotime($gdp->getday)){
			$item_data = $feed->get_item($x);
			$feed_data = $item_data->get_feed();

			//前項と重複してるかどうか
			if($num == 0){
			}elseif($first_items[$num]['sitehash'] === $first_items[$num - 1]['sitehash']){
				continue;
			}

			$text = $item_data->get_title();// 対象文字列
			if(preg_match($gdfa->BAN_WORD,$text)){
				continue; // 単語が入っていれば取得対象外
			}

			//アンテナ別フィルター
			//$filter_word = "none";
			$filter_word = $ant_data['filter'];
			if($filter_word === "none"){
			}elseif(preg_match($filter_word,$text) == false){
				continue;
			}

			$dc_cut_feed_title = str_replace("\"", "", $feed_data->get_title());
			$dc_cut_item_title = str_replace("\"", "", $item_data->get_title());

			$dc_cut_feed_title = mb_convert_encoding($dc_cut_feed_title, 'HTML-ENTITIES', 'UTF-8');
			$dc_cut_feed_title = mb_convert_encoding($dc_cut_feed_title, 'UTF-8' , 'HTML-ENTITIES');
			$dc_cut_item_title = mb_convert_encoding($dc_cut_item_title, 'HTML-ENTITIES', 'UTF-8');
			$dc_cut_item_title = mb_convert_encoding($dc_cut_item_title, 'UTF-8' , 'HTML-ENTITIES');

			$img = '';
			$content_encoded = $item_data->get_content();

			//画像抜き出し
			preg_match_all('/<img(?:.*?)src=[\"\'](.*?)[\"\'](?:.*?)>/e', $content_encoded, $match);
			if(count($match[1]) > 0){
				foreach ($match[1] as $val){
					if(preg_match('/(.jpg)|(.png)|(.gif)/',$val)){
						$img = $val;
						break;
					}else{
						$img = '';
						//break;
					}
				}
			}

			$content_encoded = mb_convert_encoding($content_encoded, 'HTML-ENTITIES', 'UTF-8');
			$content_encoded = mb_convert_encoding($content_encoded, 'UTF-8' , 'HTML-ENTITIES');

			$first_items[$num] = array(
				'item' => $feed->get_item($x), //アイテム本体
				'timestamp' => $item_data->get_date('Y-m-d H:i'), //時間
				'sitename' => $dc_cut_feed_title, //サイトタイトル
				'siteurl' => $feed_data->get_permalink(), //サイトurl
				'articlename' => sqlite_escape_string($dc_cut_item_title), //記事タイトル
				'articleurl' => $item_data->get_permalink(), //記事url
				'articlehash' => makeRandHash(32), //記事ハッシュ
				'imgurl' => $img, //イメージurl
				'articlecontent' => sqlite_escape_string($content_encoded), //記事文章
				'sitehash' => $url['sitehash'], //サイトハッシュ
				'sitecate' => $url['sitecate'], //カテゴリ名
				'clickcount' => '0', //クリックカウント
				'rssnum' => '0', //RSS
				'rssall' => '0', //RSSの総合判定
				'antennaid' => $url['antennaid'] //アンテナID
				);
			print_debug($num." ".$first_items[$num]['sitename']." ".$first_items[$num]['articlename']." ".$first_items[$num]." ".$first_items[$num]['rssall']);
			++$cnt;
		}
    }
    unset($feed);
}
//デバッグ
//var_dump($first_items);

//ソート
$timestamp = array();
foreach($first_items as $key => $row){
	//$item[$key] = $row['item']->get_date('Y-m-d H:i');
	$timestamp[$key] = $row['timestamp'];
}
array_multisort($timestamp,SORT_DESC,$first_items);

//RSS追加
$incount = array();
foreach($feeds as $key => $row){
	$incount[$key] = get_Reduction_Rate($row['incount'],$row['outcount']);
}
array_multisort($incount,SORT_DESC,$feeds);



//サイト別設定値
$gdf = new SITE_EACH_DATA($gdb,$antid);
//カテゴリー
$gct = new GET_CATEGORY($gdf->ANTENNA_ID,$gdf->CATEGORY,$gdf->CATEGORY_RSS_MAX);

$newline_data = 99;
$first_rss = true;

//カテゴリ消してもいいやつ
$cate_ori = $gct->c_origin();

//サイト
foreach($feeds as $url){
	//取得フィードで回す　rssnum反映させるため&入り
	foreach($first_items as &$items){
		//アンテナIDチェック
		if($items['antennaid'] !== $gdf->ANTENNA_ID){
			continue;
		}
		//上から順にサイトを見つけて
		if($items['sitehash'] === $url['sitehash']){
			//最初の
			if($first_rss){
				$cate = $gct->c2r($items['sitecate']);//カテゴリ
				$items['rssnum'] = $gdp->rss_name.sprintf("%02d", $cate);
				$items['rssall'] = 99;
				$first_rss = false;
				//総合RSS
				print_debug($items['articlename']);
				rss_create($gdb,$gct,$gdf,$items,$cate,$gdp->getday,99);
				//総合RSS1時間
				if($gdp->rss_one_hour < 10){
					$items['rssall'] = 60;
					rss_create($gdb,$gct,$gdf,$items,$cate,$gdp->getday,60);
				}
			}

			//カテゴリ、RSS追加ごとにどんどん減っていくカテゴリ
			foreach($cate_ori as $key => $cate){
				if($key === $url['sitecate']){
					if($items['rssnum'] == 0){
						//$newline_data = $gct->c2r($cate_i);
						$items['rssnum'] = $gdp->rss_name.sprintf("%02d", $cate);
					}
					//カテゴリ別RSS
					rss_create($gdb,$gct,$gdf,$items,$cate,$gdp->getday);

					//追加したカテゴリ削除
					$temp_arr = array_keys($cate_ori,$cate);
					print_debug("カテゴリ削除----------------");
					foreach($temp_arr as $temp){
						print_debug($cate_ori[$temp]);
						unset($cate_ori[$temp]);
					}
					break;
				}
			}
		}
	}
}

//ランキング用ソート
$incount = array();
foreach($feeds as $key => $row){
	$incount[$key] = $row['incount'];
}
array_multisort($incount,SORT_DESC,$feeds);
//アクセスランキング出力
$cdt->in_rank_dat($feeds,$gdf->FOLDER_DAT.$gdf->DAT_RANKING);

unset($gdf);
unset($gct);

//--------------記事DB書込
$file_path = $gdp->get_Feed_Path();
//DB書込
$gdb->set_FeedData($file_path,$gdfa->TABLE_ARTICLE,$first_items,false);

?>
