<?php

require_once('define.php');

class SiteData_Array
{
	public $sitename;
	public $rssurl;
	public $sitecate;
	public $sitehash;

	function DataPush($ArrayData){
		$this->sitename = $ArrayData['sitename'];
		$this->rssurl = $ArrayData['rssurl'];
		$this->sitecate = $ArrayData['sitecate'];
		$this->sitehash = $ArrayData['sitehash'];
	}
}

class FeedData_Array
{
	public $item;
	public $sitehash;
	public $category;

	function DataPush($ArrayData){
		$this->item = $ArrayData['item'];
		$this->sitehash = $ArrayData['sitehash'];
		$this->category = $ArrayData['category'];
	}
}



//カテゴリー取得クラス
class GET_CATEGORY{
	//RSSカテゴリ個数
	public $category_rss_max;
	//--カテゴリー詳細
	public $category = array();
	//カテゴリRSS用
	public $category_rss = array();
	//アンテナID
	public $antennaid;

	//初期化
	function __construct($antid,$cate,$cate_max){
		$this->category_rss_max = $cate_max;
		$this->category = $cate;
		$this->antennaid = $antid;

		for($i = 0;$i < $this->category_rss_max;$i++){
		//while($cnt < count($this->category_rss)){
			$temp = array_keys($this->category,$i);
			//スペースつけて連結する
			$this->category_rss[$i] = implode(" ", $temp);
		}
	}

	//カテゴリー配列そのまま
	public function c_origin(){
		return $this->category;
	}

	//カテゴリー配列
	public function c_data(){
		return array_keys($this->category);
	}

	//カテゴリ詳細配列
	public function c_detail(){
		return array_keys($this->category);
	}

	//カテゴリRSS用配列
	public function c_rss(){
		return $this->category_rss;
	}

	//カテゴリー変換　数字->文字
	public function r2c($cate){
		if($cate == 99){
			return $cate;
		}else{
			$arr = array_keys($this->category);
			return $arr[$cate];
		}
	}

	//カテゴリー変換　文字->数字
	public function c2r($cate){
		return $this->category[$cate];
	}
}

//DATファイル生成用クラス
class CREATE_DAT{
	//アクセスカウントDat出力
	public function in_rank_dat($feeds,$ranking_path){
		//$file_path = g_FOLDER_DAT.g_DAT_RANKING;
		$file_path = $ranking_path;

		$count = count($feeds);
		if($count > 30){
			$count = 30;
		}

		//ファイル作成
		if(touch($file_path)){
			//ファイルのパーティションの変更
			if(chmod($file_path,0644)){
			}else{exit;}
		}else{exit;}

		//ファイルをオープン
		if($filepoint = fopen($file_path,"w")){
		}else{exit;}
		//ファイルのロック
		if(flock($filepoint, LOCK_EX)){
		}else{exit;}


		$i = 1;
		//ファイル書き込み
		//fwrite($filepoint,"<div id=\"in_rank\">");
		//for($i = 1; $i <= $count; $i++){
		foreach($feeds as $item){
			//$item = $feeds[$i-1];
			if($item['incount'] < 1){
				continue;
			}
			if($i > $count){
				break;
			}
			fwrite($filepoint,"<li><span class=\"rank\">".$i."</span><a href=\"".$item['siteurl'].
				"\" target=\"_blank\" onclick=\"ccnt('".$item['sitehash']."','link','link');\"><span class=\"title\">".$item['sitename'].'</span></a></li>');
			++$i;
		}//target="_blank" onclick="ccnt("XywpMmyS","N4bQWSUEy8QOjARcIvkZgQ7aZYiUSxbb","20140904");"
		//fwrite($filepoint,"</div>");

		//アンロック
		if(flock($filepoint, LOCK_UN)){
		}else{exit;}
		//ファイルを閉じる
		if(fclose($filepoint)){
		}else{exit;}
	}
}

//逆アクセスランキング用クラス
class GET_REACCESS{
	//リファラー
	private $ref;
	//IP
	private $ip;

	//初期化
	function __construct(){
		//リファラー取得
		$ref = $_SERVER["HTTP_REFERER"];
		//IP取得
		$ip = $_SERVER["REMOTE_ADDR"];
	}

	function get_info(){

	}
}

class GET_DATE_TIME{

	//現時間
	public $today;
	//10分前
	public $getday;
	//日付数値だけ版
	public $num_date;
	//時間数値だけ版
	public $num_time;
	//表示用日付
	public $str_date;
	//表示用日付前日用
	public $str_date_ytd;
	//記事DBのフォルダパス
	public $directory_name;
	//現時刻のDB名
	public $db_name;
	//現時刻のRSS名
	public $rss_name;
	//RSS1時間用
	public $rss_one_hour;
	//今日の日付かどうかの判定
	public $today_flg;

	//DBフォルダ
	public $path;

	//初期化
	function __construct($db_folder){
		$this->path = $db_folder;

		$this->today = date('Y-m-d H:i');
		$this->getday = date('Y-m-d H:i', strtotime('-10 minutes' . $this->today));
		$this->num_date = date('Ymd', strtotime('-10 minutes' . $this->today));
		$this->str_date = date('[Y年m月d日]', strtotime('-10 minutes' . $this->today));
		$this->directory_name = $this->path.date('Ym', strtotime('-10 minutes' . $this->today));
		$this->db_name = date('Ymd', strtotime('-10 minutes' . $this->today)).".db";
		$this->rss_name = date('ymdHi', strtotime('-10 minutes' . $this->today));
		$this->rss_one_hour = date('i', strtotime('-10 minutes' . $this->today));
		$this->today_flg = true;
	}

	//日付設定
	function set_Date($date){
		//今日の日付と一緒ではないか
		if($this->today_flg && $date !== date('Ymd', strtotime($this->getday))){
			$this->today = date('Y-m-d', strtotime($date)).' 10:00';
			$this->today_flg = false;
		}else{
			return false;
		}
		//$this->today = date('Y-m-d H:i', strtotime($date));
		$this->getday = date('Y-m-d H:i', strtotime('-10 minutes' . $this->today));
		$this->num_date = date('Ymd', strtotime('-10 minutes' . $this->today));
		$this->str_date = date('[Y年m月d日]', strtotime('-10 minutes' . $this->today));
		$this->directory_name = $this->path.date('Ym', strtotime('-10 minutes' . $this->today));
		$this->db_name = date('Ymd', strtotime('-10 minutes' . $this->today)).".db";
		$this->rss_name = date('ymdHi', strtotime('-10 minutes' . $this->today));
		$this->rss_one_hour = date('i', strtotime('-10 minutes' . $this->today));
		return true;
	}

	//日付時間設定
	function set_Date_Time($date){
		$this->today = date('Y-m-d H:i', strtotime($date));
		$this->getday = date('Y-m-d H:i', strtotime('-10 minutes' . $this->today));
		$this->num_date = date('Ymd', strtotime('-10 minutes' . $this->today));
		$this->str_date = date('[Y年m月d日]', strtotime('-10 minutes' . $this->today));
		$this->directory_name = $this->path.date('Ym', strtotime('-10 minutes' . $this->today));
		$this->db_name = date('Ymd', strtotime('-10 minutes' . $this->today)).".db";
		$this->rss_name = date('ymdHi', strtotime('-10 minutes' . $this->today));
		$this->rss_one_hour = date('i', strtotime('-10 minutes' . $this->today));
		return true;
	}

	//過去日付取得 day日付(-1=1日前,-2=2日前)
	function get_Date_Past($past){
		return date(strtotime($past.' day'.$this->getday));
		/*if($date == -1){
			$date = date('Ymd', strtotime('-1 day'));
		}elseif($date == -2){
			$date = date('Ymd', strtotime('-2 day'));
		}else{
			$date = date('Ymd');
		}
		return $date;*/
	}

	function get_Str_Date_Yesterday(){
		return 	date('[Y年m月d日]', strtotime('-1 days' . $this->getday));
	}

	//記事Path取得 day日付(0=今日,-1=1日前,-2=2日前)
	function get_Feed_Path($past = 0){
		//フォルダ名
		$feed_path = "";
		if($past == 0){
			$feed_path = $this->directory_name."/".$this->db_name;
		}else{
			$date = date('Ymd', strtotime($past.' day'.$this->getday));
			$directory_name = $path.date('Ym', strtotime($date));
			//DB名
			$db_name = date('Ymd', strtotime($date)).".db";
			//記事Path
			$feed_path = $directory_name."/".$db_name;
		}
		return $feed_path;
	}

}

//還元率計算
function get_Reduction_Rate($incnt,$outcnt){
	return ($incnt+0.1) / ($outcnt+1);
}


//デッドリンクチェック
function get_http_header( $target ) {

    // URIから各情報を取得
    $info = parse_url( $target );

    $scheme = $info['scheme'];
    $host = $info['host'];
    $port = $info['port'];
    $path = $info['path'];
    // ポートが空の時はデフォルトの80にします。
    if( ! $port ) {
        $port = 80;
    }

    // リクエストフィールドを制作。
    $msg_req = "HEAD " . $path . " HTTP/1.0\r\n";
    $msg_req .= "Host: $host\r\n";
    $msg_req .=
        "User-Agent: H2C/1.0\r\n";
    $msg_req .= "\r\n";

    // スキームがHTTPの時のみ実行
    if ( $scheme == 'http' ) {

        $status = array();

        // 指定ホストに接続。
        if ( $handle = @fsockopen( $host, $port, $errno, $errstr, 1 ) ) {

            fputs ( $handle, $msg_req );

            if ( socket_set_timeout( $handle, 3 ) ) {

                $line = 0;
                while( ! feof( $handle) ) {

                    // 1行めはステータスライン
                    if( $line == 0 ) {
                        $temp_stat =
                            explode( ' ', fgets( $handle, 4096 ) );
                        $status['HTTP-Version'] =
                            array_shift( $temp_stat );
                        $status['Status-Code'] = array_shift( $temp_stat );
                        $status['Reason-Phrase'] =
                            implode( ' ', $temp_stat );

                    // 2行目以降はコロンで分割してそれぞれ代入
                    } else {
                        $temp_stat =
                            explode( ':', fgets( $handle, 4096 ) );
                        $name = array_shift( $temp_stat );
                        // 通常:の後に1文字半角スペースがあるので除去
                        $status[ $name ] =
                            substr( implode( ':', $temp_stat ), 1);
                    }
                    $line++;
                }

            } else {
                    $status['HTTP-Version'] = '---';
                    $status['Status-Code'] = '902';
                    $status['Reason-Phrase'] = "No Response";
            }

            fclose ( $handle );

        } else {
            $status['HTTP-Version'] = '---';
            $status['Status-Code'] = '901';
            $status['Reason-Phrase'] = "Unable To Connect";
        }


    } else {
        $status['HTTP-Version'] = '---';
        $status['Status-Code'] = '903';
        $status['Reason-Phrase'] = "Not HTTP Request";
    }

    return $status;

}

//アクセスカウンター
class GET_COUNTER{
	//ログ形式は　今日の日付け|昨日のｶｳﾝﾄ|今日のｶｳﾝﾄ|合計ｶｳﾝﾄ|直前IP
	//------------設定----------
	//テキストカウンタなら0 画像カウンタなら1
	private $mode;
	// 総カウント用GIF画像のディレクトリ
	private $all_path;
	// 本日カウント用GIF画像のディレクトリ
	private $day_path;
	// 昨日カウント用GIF画像のディレクトリ
	private $yes_path;
	// カウンタ記録ファイル
	private $log_path;
	// 昨日カウント数の桁数
	private $fig1;
	// 本日カウント数の桁数
	private $fig2;
	// 合計カウント数の桁数
	private $fig3;
	// 連続IPはカウントしない（yes=1 no=0)
	private $ipcheck;
	//---------設定ここまで------

	//初期化
	function __construct($count_path){
		$this->mode = 1;
		$this->all_path = './cgi-bin/count/gif/';
		$this->day_path = './cgi-bin/count/gif/';
		$this->yes_path = './cgi-bin/count/gif/';
		$this->log_path = $count_path;//g_FOLDER_DAT.g_DAT_COUNT;
		$this->fig1 = 7;
		$this->fig2 = 7;
		$this->fig3 = 10;
		$this->ipcheck = 1;
	}

	//カウント数とパスを与えて、IMGタグを返す
	private function outhtml($f_cnt, $c_path){
		$size = getimagesize($c_path."0.gif");  //0.gifからwidthとheight取得
		for($i=0; $i<strlen($f_cnt); $i++){	//桁数分だけループ
			$n = substr($f_cnt, $i, 1);	//左から一桁ずつ取得
			$i_tag.="<IMG SRC=\"$c_path$n.gif\" alt=$n $size[3]>";
		}
		return $i_tag;
	}

	//カウンター
	function counter(){
		$now_date = date('Ymd');	// 今日の日付
		$yes_date = date('Ymd', strtotime('-1 day'));	// 昨日の日付
		$dat = file($this->log_path);			//配列にログ読み込む
		$ip = $_SERVER['REMOTE_ADDR'];       //IPアドレス

		//変数を展開（比較用日付、昨日、今日、総合、直前IP）
		list($key, $yes, $tod, $all, $addr)=explode("|", $dat[0]);

		if(($this->ipcheck && $ip != "$addr") || $this->ipcheck==0){
			if($key == $now_date){//ログの日付が今日ならカウントアップ
				$tod++;
			}else{//日付がかわったら昨日に今日、今日に１を入れる。昨日じゃないなら昨日に0
				$yes = ($key == $yes_date) ? $tod : 0;
				$tod = 1;
			}
			$all++;//合計カウントアップ

			//桁数整形
			$yes = sprintf("%0".$fig1."d", $yes);
			$tod = sprintf("%0".$fig2."d", $tod);
			$all = sprintf("%0".$fig3."d", $all);
			//更新
			$new = implode("|", array($now_date,$yes,$tod,$all,$ip));
			$fp = fopen($this->log_path, "r+");
			flock($fp, LOCK_EX);
			fputs($fp, $new);
			fclose($fp);
		}
		//桁数整形
		$yesterday = sprintf("%0".$this->fig1."d", $yes);
		$today = sprintf("%0".$this->fig2."d", $tod);
		$total = sprintf("%0".$this->fig3."d", $all);

		if($this->mode){
			//タグを取得（画像出力）
			$yesterday = outhtml($yesterday, $this->yes_path);
			$today = outhtml($today, $this->day_path);
			$total = outhtml($total, $this->all_path);
		}

		$countarray = array($total,$today,$yesterday);
		return $countarray;
	}
}

//ボットか判別
function isRobot($UserAgent) {
	if(!$UserAgent){
		$UserAgent = $_SERVER['HTTP_USER_AGENT'];
	}
	$robot="/(ICC-Crawler|Teoma|Y!J-BSC|Pluggd\/Nutch|psbot|CazoodleBot|
		Googlebot|Antenna|BlogPeople|AppleWebKitOpenbot|NaverBot|PlantyNet|livedoor|
		msnbot|FlashGet|WebBooster|MIDown|moget|InternetLinkAgent|Wget|InterGet|WebFetch|
		WebCrawler|ArchitextSpider|Scooter|WebAuto|InfoNaviRobot|httpdown|Inetdown|Slurp|
		Spider|^Iron33|^fetch|^PageDown|^BMChecker|^Jerky|^Nutscrape|Baiduspider|TMCrawler)/m";

	if(preg_match($robot,$UserAgent) || ereg($robot,$UserAgent)) {
		return true;
	}else{
		return false;
	}
}

//エラーログ
function error_logger($target,$log_folder){
	//header('Content-type:text/html; charset=utf-8');
	$fh = fopen($log_folder.'error.log','a');
	fwrite($fh, date('Y/m/d H:i:s')."\t".$target."\n");
	fclose($fh);
}

//デバッグ用
function print_debug($str){
	if(s_Debug_out){
		echo "debag: ".$str."<br>\n";
	}
}

//日付取得 day日付(-1=1日前,-2=2日前)
function get_Date_Ymd($day){
	if($day == -1){
		$day = date('Ymd', strtotime('-1 day'));
	}elseif($target == -2){
		$day = date('Ymd', strtotime('-2 day'));
	}else{
		$day = date('Ymd');
	}
	return $target;
}

//記事Path取得
function get_Feed_Path($date,$db_folder){
	//フォルダ名
	$directory_name = $db_folder.date('Ym', strtotime($date));
	//DB名
	$db_name = date('Ymd', strtotime($date)).".db";
	//記事Path
	$feed_path = $directory_name."/".$db_name;
	return $feed_path;
}

//ハッシュ生成
function makeRandHash($length = 32){
    static $chars;
    if (!$chars) {
        $chars = array_flip(array_merge(
            range('a', 'z'), range('A', 'Z'), range('0', '9')
        ));
    }
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= array_rand($chars);
    }
    return $str;
}

//自身のアンテナID取得
function get_Antenna_ID_Self(){
	$fileName = './sitedef.php';
	$file = fopen($fileName, "r");
	$str = "";
	while (!feof($file)) {
		$str = fgets($file);
	}
	fclose($file);
	return $str;
}

function set_Antenna_ID_Self($folder_path,$antid){
	$path = $folder_path."/sitedef.php";
	$fp = fopen($path,'w');
	fputs($fp,$antid);
	fclose($fp);
	return true;
}

?>
