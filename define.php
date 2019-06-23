<?php
require_once('db.php');
//サイト設定基本
class SITE_FIX{

	//取得拒否ワード
	public $BAN_WORD = '/NGワード1|NGワード2|NGワード3/';

	//アクセス変換レート（これ以下の場合は次の週記事取得しない)
	public $NUM_ACCESS_RATE = 10;
	//相互感知（相互リンクされたんだなと感知する週クリック数）
	public $NUM_EACH_RATE = 30;

	//--メッセージ
	public $MSG_NO_OPEN = 'ファイルが開けません';

	//--マスターネーム
	public $SITE_MASTER_TITLE = 'アンテナ名';
	//--マスターURL
	public $SITE_MASTER_URL = 'http://www.example.com/';
	//--マスターユーザーネーム
	public $SITE_MASTER_AUTHER = 'poter';

	//--マスターメールアドレス
	public $MAIL_ADDRESS_MASTER = 'example@example.com';

	//--外部フォルダ
	public $FOLDER_DB = '../../antenna/example/db/';
	public $FOLDER_MASTER_DATA = './pkg/';

	//--戻りフォルダ位置
	public $FOLDER_UP = '../../';

	//--外部ファイル
	//リンクDB
	public $DB_LINK = 'link.db';
	//PickUpDB
	public $DB_PICKUP = 'pickup.db';
	//アンテナDB
	public $DB_ANTENNA = 'antenna.db';

	//DBのテーブル名
	public $TABLE_SITE = 'site_data';
	public $TABLE_ARTICLE = 'article_data';
	public $TABLE_PICKUP = 'pickup_data';
	public $TABLE_RANKING = 'ranking_data';
	public $TABLE_ANTENNA = 'antenna_data';

	//NoImage
	public $IMG_NOIMG = 'http://example.com/img/noimg.png';

	//DATファイル名
	public $DAT_RANKING = 'ranking.dat';
	public $DAT_PICKUP = 'pickup.dat';
	public $DAT_ABOUT = 'about.dat';
	public $DAT_ALL = 'all.dat';
	public $DAT_COUNT = 'count.dat';
	public $DAT_INFO = 'info.dat';
	public $DAT_SIGNUP = 'signup.dat';

	public $DAT_AD_FRONT = 'adfront.dat';
	public $DAT_AD_BACK = 'adback.dat';

	function Excange_Folder_DB(){
		$this->FOLDER_DB = $this->FOLDER_UP.$this->FOLDER_DB;
	}
	/*public function GET_DB_Antenna(){
		//DB接続
		try {
			$db = new PDO('sqlite:'.$path);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//print("DB接続成功<br>");
		} catch (PDOException $e) {
			die ('Connection failed : '.$e->getMessage());
		}
	}*/
}

//サイト個別用(クラスでDB引っ張ってくる用)
class SITE_EACH_DATA extends SITE_FIX{
	public $SITE_TITLE = 'アンテナ名';
	public $SITE_SUBTITLE = 'サブタイトル';
	public $SITE_SEARCHWORD = 'アンテナサイト,サーチワード';
	public $SITE_COMMENT = 'サイト説明';
	//サイトURL
	public $SITE_URL = 'http://example.com/';
	//サイトRSSURL
	public $SITE_RSSURL = 'http://example.com/?t=';
	//管理者名
	public $SITE_AUTHER = '管理者名';
	//メールアドレス
	public $MAIL_ADDRESS = 'example@example.com';
	//データ表示開始日付
	public $START_DATE = 20100101;
	//PickUpに表示する数
	public $PICKUP_PC = 8;
	//PickUpに表示する数（スマホ）
	public $PICKUP_SP = 3;
	//RSSからの表示PickUp数
	public $PICKUP_RSS = 49;
	//通常表示記事数
	public $LIST_MAX = 50;

	//外部ファイル
	public $FOLDER_LOG = '../../antenna/example/log/';
	public $FOLDER_DAT = '../../antenna/example/dat/';
	public $FOLDER_RSS = './rss/';

	//アンテナID
	public $ANTENNA_ID = 1;
	//カテゴリー
	public $CATEGORY_RSS_MAX = 4;
	public $CATEGORY = array();

	//初期化
	function __construct(GET_SET_DB $gdb,$antid){
		$ant = array();
		$ant = $gdb->get_AntennaData($gdb->FOLDER_DB.$gdb->DB_ANTENNA,$antid);

		$this->SITE_TITLE = $ant['title'];
		$this->SITE_SUBTITLE = $ant['subtitle'];
		$this->SITE_SEARCHWORD = $ant['searchword'];
		$this->SITE_COMMENT = $ant['comment'];
		//サイトURL
		$this->SITE_URL = $ant['url'];
		//サイトRSSURL
		$this->SITE_RSSURL = $ant['rssurl'];
		//管理者名
		$this->SITE_AUTHER = $ant['auther'];
		//メールアドレス
		$this->MAIL_ADDRESS = $ant['mailaddress'];
		//データ表示開始日付
		$this->START_DATE = $ant['startdate'];
		//PickUpに表示する数
		$this->PICKUP_PC = $ant['pickuppc'];
		//PickUpに表示する数（スマホ）
		$this->PICKUP_SP = $ant['pickupsp'];
		//RSSからの表示PickUp数
		$this->PICKUP_RSS = $ant['pickuprss'];
		//通常表示記事数
		$this->LIST_MAX = $ant['listmax'];

		//外部ファイル
		$this->FOLDER_LOG = $ant['folderlog'];
		$this->FOLDER_DAT = $ant['folderdat'];
		$this->FOLDER_RSS = $ant['folderrss'];

		//アンテナID
		$this->ANTENNA_ID = $ant['antennaid'];
		//カテゴリー
		$this->CATEGORY_RSS_MAX = $ant['categoryrssmax'];
		$this->CATEGORY = json_decode($ant['category'],true);//カテゴリーjsonデコード
	}

	function Excange_Folder_LOG(){
		$this->FOLDER_LOG = $this->FOLDER_UP.$this->FOLDER_LOG;
	}
	function Excange_Folder_DAT(){
		$this->FOLDER_DAT = $this->FOLDER_UP.$this->FOLDER_DAT;
	}
}

//デバッグ出力するか
define("s_Debug_out",false);

?>
