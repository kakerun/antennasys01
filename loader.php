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

//アンテナ全部取得
$ant_all = $gdb->get_AntennaData_All($gdfa->FOLDER_DB.$gdfa->DB_ANTENNA);
//$gdp->set_Date_Time($date_time);

//--------------記事DB作成
$file_path = $gdp->get_Feed_Path();
$table_create_flag = false;//これいらない？
//ディレクトリが存在するかを調べる
if( file_exists( $gdp->directory_name ) ){
}else{
	//ディレクトリ（フォルダ）を作成する。
	if (mkdir( $gdp->directory_name, 0755 )){
	}else{
		//失敗
		exit;
	}
}
//DBが存在するかを調べる
if( file_exists( $file_path ) ){
}else{
	$table_create_flag = true;
	//存在しなければDB作成
	$gdb->make_DB($file_path,$gdfa->TABLE_ARTICLE,1);
}

//処理時間計測
$sw_start = microtime(true);

/**
 * curl_multiでHTTP複数リクエストを並列実行するテンプレ
 *
 */
//タイムアウト時間を決めておく
$TIMEOUT = 120; //120秒

/*
 * 1) 準備
 *  - curl_multiハンドラを用意
 *  - 各リクエストに対応するcurlハンドラを用意
 *    リクエスト分だけ必要
 *    * レスポンスが必要な場合はRETURNTRANSFERオプションをtrueにしておくこと。
 *  - 全てcurl_multiハンドラに追加
 */
$mh = curl_multi_init();

//url
$urls = array();
foreach($ant_all as $item){
	array_push($urls,$gdfa->SITE_MASTER_URL.'getfeed.php?a='.$item["antennaid"]);
}

foreach ($urls as $u) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => $u,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => $TIMEOUT,
    ));
    curl_multi_add_handle($mh, $ch);
}

/*
 * 2) リクエストを開始する
 *  - curl_multiでは即座に制御が戻る（レスポンスが返ってくるのを待たない）
 *  - いきなり失敗するケースを考えてエラー処理を書いておく
 *  - do～whileはlibcurl<7.20で必要
 */
do {
    $stat = curl_multi_exec($mh, $running); //multiリクエストスタート
} while ($stat === CURLM_CALL_MULTI_PERFORM);
if ( ! $running || $stat !== CURLM_OK) {
    throw new RuntimeException('リクエストが開始出来なかった。マルチリクエスト内のどれか、URLの設定がおかしいのでは？');
}

/*
 * 3) レスポンスをcurl_multi_selectで待つ
 *  - 何かイベントがあったらループが進む
 *    selectはイベントが起きるまでCPUをほとんど消費せずsleep状態になる
 *  - どれか一つレスポンスが返ってきたらselectがsleepを中断して何か数字を返す。
 *
 */
do switch (curl_multi_select($mh, $TIMEOUT)) { //イベントが発生するまでブロック
    // 最悪$TIMEOUT秒待ち続ける。
    // あえて早めにtimeoutさせると、レスポンスを待った状態のまま別の処理を挟めるようになります。
    // もう一度curl_multi_selectを繰り返すと、またイベントがあるまでブロックして待ちます。

    case -1: //selectに失敗。ありうるらしい。 https://bugs.php.net/bug.php?id=61141
        usleep(10); //ちょっと待ってからretry。ここも別の処理を挟んでもよい。
        do {
            $stat = curl_multi_exec($mh, $running);
        } while ($stat === CURLM_CALL_MULTI_PERFORM);
        continue 2;

    case 0:  //タイムアウト -> 必要に応じてエラー処理に入るべきかも。
        continue 2; //ここではcontinueでリトライします。

    default: //どれかが成功 or 失敗した
        do {
            $stat = curl_multi_exec($mh, $running); //ステータスを更新
        } while ($stat === CURLM_CALL_MULTI_PERFORM);

        do if ($raised = curl_multi_info_read($mh, $remains)) {
            //変化のあったcurlハンドラを取得する
            $info = curl_getinfo($raised['handle']);
            echo "{$info['url']}: {$info['http_code']}\n";
            $response = curl_multi_getcontent($raised['handle']);

            if ($response === false) {
                //エラー。404などが返ってきている
                echo 'ERROR!!!', PHP_EOL;
            } else {
                //正常にレスポンス取得
                echo $response, PHP_EOL;
            }
            curl_multi_remove_handle($mh, $raised['handle']);
            curl_close($raised['handle']);
        } while ($remains);
        //select前に全ての処理が終わっていたりすると
        //複数の結果が入っていることがあるのでループが必要

} while ($running);
//echo 'finished', PHP_EOL;
curl_multi_close($mh);
//マルチスレッド終了

//------pickup

//アンテナの数だけループ
foreach($ant_all as $ant){

	//サイト別設定値
	$gdf = new SITE_EACH_DATA($gdb,$ant['antennaid']);
	//カテゴリー
	$gct = new GET_CATEGORY($gdf->ANTENNA_ID,$gdf->CATEGORY,$gdf->CATEGORY_RSS_MAX);

	pickup_create($gdb,$gct,$gdf,$gdp);
	unset($gdf);
	unset($gct);
}

//-------アクセスカウントリセット
if(date('D', strtotime($gdp->getday)) === "Mon" && date('Hi', strtotime($gdp->getday)) === "0430"){
	//月曜9時10分にリセット
	$feeds = $gdb->get_SiteData($gdfa->FOLDER_DB.$gdfa->DB_LINK);
	$gdb->set_Count_Reset($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$feeds);
	//ファイルの中身を空にする
	$fp = fopen('data.php', 'r+');
	flock($fp, LOCK_EX);
	//2番目の引数のファイルサイズを0にして空にする
	ftruncate($fp,0);
	flock($fp, LOCK_UN);
	fclose($fp);
}

$sw_end = microtime(true);
$sw = $sw_end - $sw_start;
error_logger($sw,"");
//}

?>
