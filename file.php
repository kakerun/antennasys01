<?php
require_once('define.php');
require_once('db.php');

function reaccess($array_data,$link_url,SITE_EACH_DATA $gdf,GET_SET_DB $gdb){
require_once('lib.php');
require_once('html.php');
//header('Content-type:text/html; charset=utf-8');
//syoki();

//初期化とか---------------------------------------
//ブロックするＵＲＬ
//設定例：$block_url=array("www.abc.com","www.vvv.jp/asd/");
$block_url=array("example.com","feeda.info/publish/");

$kankaku=9; //更新日数間隔
$rankmax=30; //表示されるランク数
$haba=150; //表の横幅
$kensaku=true;//検索エンジン等を表示させる場合は「true」、表示させない場合は「false」


//$kari_url=$_GET['url'];
$kari_url=$_SERVER["HTTP_REFERER"];
$url2=explode('?',$kari_url);
$url=$url2[0];
$site[$url2[0]]=$url2[0];

//yomikae();
$site_url;
#ＵＲＬ読み替え 「http://」はすべて外す
$site['yahoo.co.jp']='Yahoo Japan';
$site['msn.co.jp']='msn.jp';
$site['google.co.jp']='Google.jp';
$site['yahoo.com']='Yahoo.com';
$site['msn.com']='msn.com';
$site['google.com']='Google.com';
$site['t.co']='twitter';

//define('CSVFILE', '../link.csv');
//define('DELIMITER', "\t");//データ区切り(カンマ)
//define('ENCLOSURE', ' ');//データ囲み文字(ダブルクォーテーション)

//ファイルを開く
$array_acclink = array();
//$fp = fopen('../link.csv', 'r') or die('ファイルが開けません');
//$item_cnt = 0;
//$item = 'item';

//読み込み
$array_acclink = $array_data;

//変換
foreach($array_acclink as $items){
	$item = str_replace('http://', '', $items['siteurl']);
	$item = substr($item, 0, (strlen($item)-1));
	$site[$item] = $items['sitename'];
}
//テーブルを出力
//$site['yahoo.co.jp']='Yahoo Japan';
/*while ($item_array = fgetcsv($fp, 1758, "\t", ' ')) {
	$item = str_replace('http://', '', $item_array[2]);
	$item = substr($item, 0, (strlen($item)-1));
	//$site[mb_convert_encoding($item, "SJIS", "UTF-8")] = mb_convert_encoding($item_array[1], "SJIS", "UTF-8");
	$site[$item] = $item_array[1];
	//$item = '$site['."'".$item."']='".$item_array[1]."';";
	//array_push($array_acclink, $item);
	$item_cnt++;
}
//$line=join("\n",$array_acclink);

//$fp=fopen("link_conv.txt","w");
//fwrite($fp,$line);
fclose($fp);
*/

#逆アクセスがあったURLとは別のページのURLを表示させたいときは、コメントをはずして以下を修正。aaaaaaa.com/aaaaa/のドメインからのアクセスがあったとき、aaaaaaa.com/aaaaa/bbb.htmlに飛ばす。

#$site_url['aaaaaaa.com/aaaaa/']="aaaaaaa.com/aaaaa/bbb.html";

$blockflag=false;
foreach($block_url as $str){
	if(@ereg($str,$url))$blockflag=true;
}
//配列siteから要素$i、値$jを取り出す
foreach($site as $i=>$j){
	//配列siteに登録したurlをベースに、リファラーからドメイン以外の情報を取り除く
	$url=ereg_replace('.*'.quotemeta($i).'.*',$i,$url);
}
$url=str_replace("http://","",$url);
//$murl=$_GET['murl'];//自サイトのＵＲＬ
$murl=(empty($_SERVER["HTTPS"]) ? "http://" : "https://").$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
$k_murl=parse_url($murl);//ＵＲＬをドメインやファイル名などに分解して連想配列に格納
$murl=$k_murl["host"];//自サイトのＵＲＬにドメインだけを代入
/*
$kari=explode('/',$murl);
$murl=str_replace($kari[count($kari)-1],"",$murl);
$murl=str_replace("http://","",$murl);
*/
//ログとってみる
error_logger($kari_url." ".$murl,$gdf->FOLDER_LOG);

$ee=strpos("a".$url,$murl);//バックリンク$urlに自サイトのドメインが含まれているか？yesならば$ee>0

$year=date("Y");//例：2007
$month=date("n");//例：12
$day=date("d");//31
$hour=date("H");//23
$minut=date("i");//50
$time=mktime($hour,$minut,0,$month,$day,$year);//1970/1/1からの現在時刻における経過秒数

$ip=$_SERVER["REMOTE_ADDR"];
$acc=file($gdf->FOLDER_UP."data.php");
$kari=explode(',',$acc[0]);
//$rurl[0].",".$cnt[0].",".$rank[0].",".$ip.",".$url.",".$mim.",".$yy.",".$time.",\n"
$u1=$kari[3];//ip
$u2=$kari[4];//http://のないリファラー$url
$mim=$kari[5];//1195473480前回更新時における経過病数
$yy=($kankaku-1)*24*60*60+(24-date("G"/*時間0-23*/,$mim))*60*60+(60-date("i"/*分*/,$mim))*60;//前回更新から次回更新までの秒数
$jj=0;
$jjj=0;
$l=0;
//header('Content-Type: text/html; charset=shift_jis');

for($i=0;$i<count($acc);$i++){
	$kari=explode(',',$acc[$i]);
	$krurl=$kari[0];
	//配列siteから要素$k、値$jを取り出す
	foreach($site as $k=>$j){
	//配列siteに登録したurlをベースに、リファラーからドメイン以外の情報を取り除く
		$krurl=ereg_replace('.*'.quotemeta($k).'.*',$k,$kari[0]);
	}
	if($krurl!=$kari[0]){
		$jjj=1;//$siteに新規にＵＲＬを登録した場合は、ファイル書込みできるようにする
	}
	//echo $krurl;
	if($site2[$krurl]<1 || !$site2[$krurl]){
		$rurl[$l]=$krurl;
		$cnt[$l]=$kari[1];
		$rank[$l]=$kari[2];
		$site2[$rurl[$l]]=$l+1;
		if($mim <= $time-$yy ){//現在時秒数ー更新間隔秒数＞＝前回更新時秒数ならば更新
			if($cnt[$l]==0){$cnt[$l]=-1;}
			else{$cnt[$l]=0;}
			$rank[$l]=$l+1;
		}
		if($url == $rurl[$l]){
			$cnt[$l]++;
			$flag=1;//既存のリファラーならば$flag=1
		}
		$l++;
	}else{
		$cnt[$site2[$krurl]-1]+=$kari[1];
	}
}

if($mim <= $time-$yy ){//現在時秒数ー更新間隔秒数＞＝前回更新時秒数ならば更新
	$mim=$time;//更新時における現在時秒数を$miimに代入
	$jj=1;
}

$ii=0;//同じipからの連続アクセスかを調べる
if($u2==$url){$ii++;}
if($u1==$ip){$ii++;}
if($url && $ee<1/*自サイトのドメインでない*/ && $ii<2 && $url !='blockedReferrer'){
	if($flag != 1){//新規のリファラーならば、配列の末尾に追加
		$rurl[count($acc)]=$url;//リファラー
		$cnt[count($acc)]=1;//カウント
		$rank[count($acc)]=500;//順位
	}
	$jj=1;
}
if(!$blockflag){
	if($ee<1 && (($url && ($kensaku || ereg('\?',$kari_url)==false) && $jj==1 ) || $jjj==1)){//更新によりリセットされた、かつ自サイトのドメインでない
		array_multisort($cnt,SORT_DESC,$rurl,$rank);
		$pointer=fopen("data.php", "w");
		#flock($pointer, LOCK_EX);
		fwrite($pointer, $rurl[0].",".$cnt[0].",".$rank[0].",".$ip.",".$url.",".$mim.",".$yy.",".$time.",\n");
		for($i=1;$i<count($cnt);$i++){
			if($cnt[$i]>-1){
				fwrite($pointer, $rurl[$i].",".$cnt[$i].",".$rank[$i].",\n");
			}
		}
		#flock($pointer, LOCK_UN);
		fclose($pointer);

		$shs = "";//サイトハッシュ

		$site_data = array();
		$cnt_db = 0;
		$ret = false;//書き込みフラグ
		foreach($array_data as $items){
			$item = str_replace('http://', '', $items['siteurl']);
			$item = substr($item, 0, (strlen($item)-1));
			$cnt_url = 0;
			/*if($item === 'chikakb.ldblog.jp'){
				error_logger("--- ".$item."--- ");
				error_logger("--! ".$rurl[0]."--- ");
				$ret = array_search($item,$rurl);
				error_logger($ret." ".$cnt[$ret]." ".$rank[$ret]);
			}*/
			//サーチ
			$ret = array_search($item,$rurl);
			if($ret !== false){
				$it = array();
				$it['incount'] = $cnt[$ret];
				$it['sitehash'] = $items['sitehash'];
				$shs = $items['sitehash'];
				//$items['inrank'] = $rank[$ret];
				//DB用の配列に入れる
				//$site_data[$cnt_db] = $items;
				$site_data = $it;
				break;
				//error_logger("-------".$site_data[$cnt_db]['sitehash']);
				++$cnt_db;
				//error_logger($item." ".$items['incount']." ".$items['inrank']);

			}
		}

		//DB書込
		//$gdb = new GET_SET_DB;
		//error_logger($link_url." write");
		$gdb->set_FeedData_Count($link_url,$gdf->TABLE_SITE,$shs);//$site_data);
	}
}


/*
$im=ImageCreateFromGif("crown.gif");
header('Content-Type: image/gif');
imagepng($im);
*/
}
?>
