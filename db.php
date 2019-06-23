<?php
require_once('define.php');
//DB取得クラス
class GET_SET_DB extends SITE_FIX{
	//サイトデータ用フィールド名
	private $model_db_site_field = array(
		'site_name',
		'site_url',
		'site_hash',
		'rss_url',
		'category',
		'article_load',
		'each_link',
		'in_count',
		'out_count',
		'in_rank',
		'out_rank',
		'blogroll_url',
		'user_name',
		'mail_address',
		'other_message',
		'id',
		'password',
		'status',
		'antenna_id'
		);
	//記事データ用フィールド名
	private $model_db_feed_field = array(
		'time_stamp',
		'site_name',
		'site_url',
		'site_hash',
		'article_name',
		'article_url',
		'article_hash',
		'category',
		'img_url',
		'article_content',
		'click_count',
		'rss_num',
		'rss_all',
		'antenna_id'
		);
	//アンテナ用フィールド名
	private $model_db_antenna_field = array(
		'antenna_id',
		'title',
		'sub_title',
		'search_word',
		'comment',
		'url',
		'rss_url',
		'auther',
		'mail_address',
		'start_date',
		'pickup_pc',
		'pickup_sp',
		'pickup_rss',
		'list_max',
		'folder_log',
		'folder_dat',
		'folder_rss',
		'category_rss_max',
		'category',
		'filter'
		);

	//サイトデータ用モデル
	private $model_get_site = array();
	//記事データ用モデル
	private $model_get_feed = array();
	//アンテナデータ用モデル
	private $model_get_antenna = array();

	//時間降順
	private $model_desc = ' order by time_stamp desc';

	//初期化
	function __construct(){
		//連結して初期モデルを作る
		$this->model_get_site = "select ".implode(", ", $this->model_db_site_field)." from site_data";
		$this->model_get_feed = "select ".implode(", ", $this->model_db_feed_field)." from ";
		$this->model_get_antenna = "select ".implode(", ", $this->model_db_antenna_field)." from antenna_data";
	}

	//連結サイトデータ用フィールド取得
	public function get_Site_Field(){
		return implode(", ", $this->model_db_site_field);
	}
	//連結記事データ用フィールド取得
	public function get_Feed_Field(){
		return implode(", ", $this->model_db_feed_field);
	}
	//連結アンテナデータ用フィールド取得
	public function get_Antenna_Field(){
		return implode(", ", $this->model_db_antenna_field);
	}
	//?を作って返す
	public function get_Question($model){
		$data = array();
		$cnt = 0;
		$model_tmp = 0;


		switch($model){
			case $this->TABLE_SITE:
				$model_tmp = $this->model_db_site_field;
				break;
			case $this->TABLE_ANTENNA:
				$model_tmp = $this->model_db_antenna_field;
				break;
			default:
				$model_tmp = $this->model_db_feed_field;
				break;
		}

		foreach($model_tmp as $item){
			$data[$cnt] = '?';
			++$cnt;
		}
		return implode(", ", $data);
	}

	//サイト用配列返すやつ
	function db_Array_site($rows){
		return array(
			'sitename' => $rows['site_name'],
			'siteurl' => $rows['site_url'],
			'sitehash' => $rows['site_hash'],
			'rssurl' => $rows['rss_url'],
			'sitecate' => $rows['category'],
			'articleload' => $rows['article_load'],
			'eachlink' => $rows['each_link'],
			'incount' => $rows['in_count'],
			'outcount' => $rows['out_count'],
			'inrank' => $rows['in_rank'],
			'outrank' => $rows['out_rank'],
			'blogrollurl' => $rows['blogroll_url'],
			'username' => $rows['user_name'],
			'mailaddress' => $rows['mail_address'],
			'othermessage' => $rows['other_message'],
			'id' => $rows['id'],
			'password' => $rows['password'],
			'status' => $rows['status'],
			'antennaid' => $rows['antenna_id']
			);
	}
	//記事用配列返すやつ
	function db_Array_feed($rows){
		return array(
			'timestamp' => $rows['time_stamp'],
			'sitename' => $rows['site_name'],
			'siteurl' => $rows['site_url'],
			'sitehash' => $rows['site_hash'],
			'articlename' => $rows['article_name'],
			'articleurl' => $rows['article_url'],
			'articlehash' => $rows['article_hash'],
			'sitecate' => $rows['category'],
			'imgurl' => $rows['img_url'],
			'articlecontent' => $rows['article_content'],
			'clickcount' => $rows['click_count'],
			'rssnum' => $rows['rss_num'],
			'rssall' => $rows['rss_all'],
			'antennaid' => $rows['antenna_id']
			);
	}
	//アンテナ用配列返すやつ
	function db_Array_antenna($rows){
		return array(
			'antennaid' => $rows['antenna_id'],
			'title' => $rows['title'],
			'subtitle' => $rows['sub_title'],
			'searchword' => $rows['search_word'],
			'comment' => $rows['comment'],
			'url' => $rows['url'],
			'rssurl' => $rows['rss_url'],
			'auther' => $rows['auther'],
			'mailaddress' => $rows['mail_address'],
			'startdate' => $rows['start_date'],
			'pickuppc' => $rows['pickup_pc'],
			'pickupsp' => $rows['pickup_sp'],
			'pickuprss' => $rows['pickup_rss'],
			'listmax' => $rows['list_max'],
			'folderlog' => $rows['folder_log'],
			'folderdat' => $rows['folder_dat'],
			'folderrss' => $rows['folder_rss'],
			'categoryrssmax' => $rows['category_rss_max'],
			'category' => $rows['category'],
			'filter' => $rows['filter']
			);
	}

	//DB取得(DBパス,SQL文,DB種類{LinkDB->0 FeedDB->1 FeedDB単品->2 DBカウント->3},$article_load条件)
	function get_DB($path,$sql,$type,$table){
		$db;
		$cnt = 0;
		$dbdata = array();


		//DB接続
		try {
			$db = new PDO('sqlite:'.$path);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//print("DB接続成功<br>");
		} catch (PDOException $e) {
			die ('Connection failed : '.$e->getMessage());
		}
		print_debug($path);
		print_debug($type);
		print_debug($sql);

		//テーブルの存在を確かめる
		if($this->chk_Table_Exist($db,$table) == 0){
			//無かったらテーブル作る
			if($table === $this->TABLE_SITE){
				$this->make_DB_Table($db,$table);
			}elseif($table === $this->TABLE_ANTENNA){
				$this->make_DB_Table($db,$table,2);
			}else{
				$this->make_DB_Table($db,$table,1);
			}
		}

		$stmt = $db->query($sql);

		switch($type){
			//link.db
			case 0:
				while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
					if($rows['status'] == 1){
					//if($rows['article_load'] == 1){
						$dbdata[$cnt] = $this->db_Array_site($rows);
						$cnt++;
					}
				}
				break;
			//feed.db pickup.db
			case 1:
				while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
					$dbdata[$cnt] = $this->db_Array_feed($rows);
					++$cnt;
				}
				break;
			//記事単品
			case 2:
				while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
					$dbdata = $this->db_Array_feed($rows);
				}
				break;
			//antenna.db
			case 3:
				while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
					$dbdata = $this->db_Array_antenna($rows);
					++$cnt;
				}
				break;
			//カウント
			case 4:
				//$stmt->execute();
				$count = $stmt->fetchColumn();
				//$count = $stmt->rowCount();
				//DB切断
				unset($db);
				return $count;
				break;
			//すべて取得
			default:
				while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
					if($table === $this->TABLE_SITE){
						$dbdata[$cnt] = $this->db_Array_site($rows);
					}elseif($table === $this->TABLE_ANTENNA){
						$dbdata[$cnt] = $this->db_Array_antenna($rows);
					}else{
						$dbdata[$cnt] = $this->db_Array_feed($rows);
					}
					++$cnt;
				}
				break;
		}
		//DB切断
		unset($db);
		//print("DB切断<br>");

		print_debug($dbdata[0]['sitename']);
		return $dbdata;
	}

	//記事データ取得
	public function get_FeedData($path,$table,$cate,$antid = 99,$limit = 0,$offset = 0){


		//リミット
		if($limit == 0){
			$limit = "";
		}else{
			$limit = " limit ".$limit;
		}

		//開始位置
		if($offset == 0){
			$offset = "";
		}else{
			$offset = " offset ".$offset;
		}

		//カテゴリーとアンテナID
		if($antid == 99 && $cate == 99){
			$cate = "";
		}elseif($cate == 99){
			$cate = " where antenna_id = '".$antid."'";
		}else{
			if($andid == 99){
				$antid = "";
			}else{
				$antid = "antenna_id = '".$antid."' and ";
			}
			$cate_split = split(" ",$cate);
			$cate = "";
			$cate = " where ".$antid."category = '".$cate_split[0]."'";
			if(count($cate_split) == 1){
			}else{
				foreach($cate_split as $cate_name){
					if($cate_name == $cate_split[0]){
						continue;
					}
					$cate = $cate." and	category '".$cate_name."'";
				}
			}
		}

		//ソート
		$sort_cmd;
		//if($table === $this->TABLE_PICKUP || $table === $this->TABLE_RANKING){
		if($table !== $this->TABLE_ANTENNA &&
			$table !== $this->TABLE_ARTICLE &&
			$table !== $this->TABLE_SITE){
			$sort_cmd = '';
		}else{
			$sort_cmd = $this->model_desc;
		}

		//SQL文
		$sql = $this->model_get_feed.$table.$cate.$sort_cmd.$limit.$offset;

		return $this->get_DB($path,$sql,1,$table);
	}

	//RSS記事データ取得
	public function get_FeedData_RSS($path,$table,$rss,$antid = 99){
		//アンテナID
		if($antid == 99){
			$antid = "";
		}else{
			$antid = " and antenna_id = '".$antid."'";
		}
		//SQL文
		$sql = $this->model_get_feed.$table." where rss_num = '".$rss."'".$antid;

		return $this->get_DB($path,$sql,2,$table);
	}

	//記事データ取得(RSSのだけ全部)
	public function get_FeedData_RSS_All($path,$table,$rssall,$antid = 99){
		//アンテナID
		if($antid == 99){
			$antid = "";
		}else{
			$antid = " and antenna_id = '".$antid."'";
		}
		//SQL文
		$sqlall = "";
		if($rssall > 0){
			$sqlall = " and rss_all > 1";
		}
		$sql = $this->model_get_feed.$table." where rss_num > 0".$sqlall.$antid; //'".$rss."'";

		return $this->get_DB($path,$sql,1,$table);
	}

	//フィードのデータ数取得
	public function get_count($path,$table,$cate,$antid = 99){
		//カテゴリーとアンテナID
		if($antid == 99 && $cate == 99){
			$cate = "";
		}elseif($cate == 99){
			$cate = " where antenna_id = '".$antid."'";
		}else{
			if($andid == 99){
				$antid = "";
			}else{
				$antid = "antenna_id = '".$antid."' and ";
			}
			$cate = " where ".$antid."category = '".$cate."'";
			/*$cate_split = split(" ",$cate);
			$cate = "";
			$cate = " where category = '".$cate_split[0]."'";
			if(count($cate_split) == 1){
			}else{
				foreach($cate_split as $cate_name){
					if($cate_name == $cate_split[0]){
						continue;
					}
					$cate = $cate." and	category '".$cate_name."'";
				}
			}*/
			//$cate = " where category = '".$cate."'";
		}
		//SQL文
		$sql = 'select count(*) from '.$table.$cate;

		return $this->get_DB($path,$sql,4,$table);
	}

	//サイトデータ取得
	public function get_SiteData($path,$antid = 99){
		//アンテナID
		if($antid == 99){
			$antid = "";
		}else{
			$antid = " where antenna_id = '".$antid."'";
		}
		//SQL文
		$sql = $this->model_get_site.$antid;

		return $this->get_DB($path,$sql,0,$this->TABLE_SITE);
	}

	//サイトデータ全て取得
	public function get_SiteData_All($path,$antid = 99){
		//アンテナID
		if($antid == 99){
			$antid = "";
		}else{
			$antid = " where antenna_id = '".$antid."'";
		}
		//SQL文
		$sql = $this->model_get_site.$antid;

		return $this->get_DB($path,$sql,99,$this->TABLE_SITE);
	}

	//サイトデータ承認変更作業用分取得
	public function get_Manage_SiteData($path,$status){
		//SQL文
		$sql = $this->model_get_site." where status = '".$status."'";
		return $this->get_DB($path,$sql,99,$this->TABLE_SITE);
	}

	//サイトデータ取得アンテナ別
	public function get_Manage_SiteData_AFilter($path,$antid){
		//SQL文
		$sql = $this->model_get_site." where antenna_id = '".$antid."'";
		return $this->get_DB($path,$sql,99,$this->TABLE_SITE);
	}

	//アンテナデータ取得
	public function get_AntennaData($path,$antid){
		//SQL文
		$sql = $this->model_get_antenna." where antenna_id = '".$antid."'";

		return $this->get_DB($path,$sql,3,$this->TABLE_ANTENNA);
	}

	//アンテナデータ全て取得
	public function get_AntennaData_All($path){
		//SQL文
		$sql = $this->model_get_antenna;

		return $this->get_DB($path,$sql,99,$this->TABLE_ANTENNA);
	}

	//テーブルが存在するかチェック 返り値が1なら存在、0なら無し
	public function chk_Table_Exist($db,$table){
		$sql = "select count(*) from sqlite_master where type='table' and name='".$table."'";
		$count;
		try {
			$stmt = $db->query($sql);
			//var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));
			//var_dump($stmt->fetch(PDO::FETCH_ASSOC));
			while($rows = $stmt->fetch(PDO::FETCH_NUM)){
				$count = $rows[0];
			}
			return $count;
		} catch (PDOException $e) {
			$err = $db->errorInfo();
			die ($err[2]);
		}
	}
//-------------------------------------------------------------
	//テーブル作成(DB,テーブル名,種類{site->0 feed->1})
	public function make_DB_Table($db,$table,$type = 0){
		$field = "";
		if($type == 0){
			$field = $this->get_Site_Field();
		}elseif($type == 1){
			$field = $this->get_Feed_Field();
		}elseif($type == 2){
			$field = $this->get_Antenna_Field();
		}

		$sql = "create table if not exists ".$table." (".$field.")";
		try {
			$stmt = $db->query($sql);
			//var_dump($stmt->fetchAll(PDO::FETCH_NUM));
		} catch (PDOException $e) {
			$err = $db->errorInfo();
			die ($err[2]);
		}
	}

	//DB作成
	public function make_DB($path,$table,$type = 0){
		$db;
		//DB接続
		try {
			$db = new PDO('sqlite:'.$path);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			print_debug("DB接続成功");
		} catch (PDOException $e) {
			die ('Connection failed : '.$e->getMessage());
		}

		if($table === $this->TABLE_SITE){
			$type = 0;
		}elseif($table === $this->TABLE_ANTENNA){
			$type = 2;
		}else{
			$type = 1;
		}

		$this->make_DB_Table($db,$table,$type);
		//DB切断
		unset($db);
		return true;
	}

	//データ全消し
	function delete_DB_All($db,$table){
		$sql = "DELETE FROM ".$table;
		try {
			$stmt = $db->query($sql);
			//var_dump($stmt->fetchAll(PDO::FETCH_NUM));
		} catch (PDOException $e) {
			$err = $db->errorInfo();
			die ($err[2]);
		}
	}

	//データ消し単品
	function delete_DB_Single($db,$table,$hash){

		$single = "";
		if($table === $this->TABLE_SITE){
			$single = " where site_hash = ".$hash;
		}elseif($table == $this->TABLE_ANTENNA){
			$single = " where antenna_id = ".$hash;
		}else{
			$single = " where article_hash = ".$hash;
		}

		$sql = "DELETE FROM ".$table.$single;
		try {
			$stmt = $db->query($sql);
			//var_dump($stmt->fetchAll(PDO::FETCH_NUM));
		} catch (PDOException $e) {
			$err = $db->errorInfo();
			die ($err[2]);
		}
	}

	//DB更新(DBパス,SQL文,DB種類{LinkDB->0 FeedDB->1 FeedDB単品->2 DBカウント->3},データ,テーブル作成フラグ)
	function set_DB($path,$sql,$type,$data,$table = 0,$tcf = false){
		$db;
		$cnt = 0;
		$dbdata = array();


		//DB接続
		try {
			$db = new PDO('sqlite:'.$path);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			print_debug("DB接続成功");
		} catch (PDOException $e) {
			die ('Connection failed : '.$e->getMessage());
		}
		print_debug($path);
		print_debug($type);
		print_debug($sql);
		//$stmt = $db->query($sql);

		//テーブル作る
		if($tcf){
			$ttype = 0;
			if($table === $this->TABLE_SITE){
				$ttype = 0;
			}elseif($table === $this->TABLE_ANTENNA){
				$ttype = 2;
			}else{
				$ttype = 1;
			}
			$this->make_DB_Table($db,$table,$ttype);
		}else{
			//テーブルの存在を確かめる
			if($this->chk_Table_Exist($db,$table) == 0){
				//無かったらテーブル作る
				echo $table;
				$ttype = 0;
				if($table === $this->TABLE_SITE){
					$ttype = 0;
				}elseif($table === $this->TABLE_ANTENNA){
					$ttype = 2;
				}else{
					$ttype = 1;
				}
				$this->make_DB_Table($db,$table,$ttype);
			}
		}
		//if($table === 'pickup_data' || $table === 'ranking_data'){
		if($table !== $this->TABLE_ANTENNA &&
			$table !== $this->TABLE_ARTICLE &&
			$table !== $this->TABLE_SITE){
			//pickupとrankingは初めてじゃない場合は全削除
			$this->delete_DB_All($db,$table);
		}

		switch($type){
			//カウンタリセット
			case 0:
				foreach($data as $item){
					//相互ではない場合は特に気にしない
					if($item['eachlink'] == 0){
						$item['articleload'] = 1;

						if($item['incount'] > $this->NUM_EACH_RATE){
							$item['eachlink'] = 1;
						}
					//inカウントが0の場合はalを0に //inカウントがoutカウントの1/○以下の場合も同様
					}elseif($item['incount'] == 0){
						$item['articleload'] = 0;
					}elseif($item['incount'] <= ($item['outcount'] / $this->NUM_ACCESS_RATE)){
						$item['articleload'] = 0;
					}else{
						$item['articleload'] = 1;
					}

					$stmt = $db->prepare($sql);
					$flag = $stmt->execute(array($item['articleload'],0,0,$item['sitehash']));
				}
				break;
			//feed.db pickup.db
			case 1:
				foreach($data as $item){
					//if ($count >= $max) { break; }
					//DB書き込み
					$stmt = $db->prepare($sql);

					$flag = $stmt->execute(array(
						$item['timestamp'], //時間
						$item['sitename'], //サイトタイトル
						$item['siteurl'], //サイトurl
						$item['sitehash'],//サイトハッシュ
						$item['articlename'], //記事タイトル
						$item['articleurl'], //記事url
						$item['articlehash'], //記事ハッシュ
						$item['sitecate'], //カテゴリ名
						$item['imgurl'], //イメージurl
						$item['articlecontent'], //記事文章
						$item['clickcount'], //クリック数
						$item['rssnum'], //RSS対象
						$item['rssall'], //RSS全種対象
						$item['antennaid'] //アンテナID
						));
					print_debug("追加 ".$item['articlename']);
					//++$count;
				}
				break;
			//単品
			case 2:
				$stmt = $db->prepare($sql);
				$flag = $stmt->execute($data);
				break;
			//inカウント
			case 3:
				foreach($data as $item){
					//error_logger($item['incount']." ".$item['inrank']." ".$item['sitehash']);
					$stmt = $db->prepare($sql);
					$flag = $stmt->execute(array($item['incount'], $item['inrank'], $item['sitehash']));
				}
				break;
			//outカウント
			case 4:
				$stmt = $db->prepare($sql);
				$flag = $stmt->execute(array($data));
				break;
			//DB削除単品
			case 50:
				try {
					$stmt = $db->query($sql);
					//var_dump($stmt->fetchAll(PDO::FETCH_NUM));
				} catch (PDOException $e) {
					$err = $db->errorInfo();
					die ($err[2]);
				}
				break;
		}
		//DB切断
		unset($db);
		print_debug("DB切断");

		//return $dbdata;
	}

	//カウントリセット
	public function set_Count_Reset($path,$table,$data){
		$mo = $this->model_db_feed_field;
		//SQL文
		//$sql = $this->model_get_feed.$table." where rss_num = '".$rss."'";
		//$sql = "update ".$table." set ".$mo[5]." = ? , ".$mo[7]." = ?, ".$mo[8]." = ? where ".$mo[2]." = ?";
		$sql = "update ".$table." set article_load = ?, in_count = ?, out_count = ? where site_hash = ?";
		$this->set_DB($path,$sql,0,$data,$table);
		return true;
	}

	//DB追記
	public function set_FeedData($path,$table,$data,$table_create_flag){

		//DB書き込み用SQL文
		$sql = "insert into ".$table." (".$this->get_Feed_Field().") values (".$this->get_Question($table).")";
		$this->set_DB($path,$sql,1,$data,$table,$table_create_flag);

		return true;
	}

	//DB単品
	public function set_FeedData_Single($path,$table,$data){
		$get_field = "";
		if($table === $this->TABLE_SITE){
			$get_field = $this->get_Site_Field();
		}elseif($table === $this->TABLE_ANTENNA){
			$get_field = $this->get_Antenna_Field();
		}
		//DB書き込み用SQL文
		$sql = "insert into ".$table." (".$get_field.") values (".$this->get_Question($table).")";
		$this->set_DB($path,$sql,2,$data,$table);
		return true;
	}

	//inアクセスカウント用
	public function set_FeedData_Count($path,$table,$data){

		//DB書き込み用SQL文
		$sql = "update ".$table." set in_count = in_count + 1 where site_hash = ?";
		//$sql = "INSERT INTO ".$table." (".$this->get_Site_Field().") VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
		$this->set_DB($path,$sql,4,$data,$table);
		return true;
	}

	//outアクセスカウント用
	public function set_FeedData_Count_Out($path,$table,$data){

		$sql;
		//DB書き込み用SQL文
		if($table === $this->TABLE_SITE){
			$sql = "update ".$table." set out_count = out_count + 1 where site_hash = ?";
		}else{
			$sql = "update ".$table." set click_count = click_count + 1 where article_hash = ?";
		}
		//$sql = "INSERT INTO ".$table." (".$this->get_Site_Field().") VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
		$this->set_DB($path,$sql,4,$data,$table);
		return true;
	}

	//管理用サイトDB更新単品
	public function set_Manage_SiteData_Single($path,$table,$data){
		//DB書き込み用SQL文
		$sql = "update ".$table." set site_name = ?, site_url = ?, rss_url = ?, category = ?, article_load = ?, each_link = ?, blogroll_url = ?, user_name = ?, mail_address = ?, other_message = ?, id = ?, password = ?, status = ?, antenna_id = ? where site_hash = ?";
		//配列のkeyを削除（そうしないと動かない）
		$data_con = array();
		$cnt = 0;
		foreach($data as $item){
			$data_con[$cnt] = $item;
			++$cnt;
		}
		$this->set_DB($path,$sql,2,$data_con,$table);
		return true;
	}

	//管理用サイトDB削除単品
	public function delete_Manage_SiteData_Single($path,$table,$data){
		//DB書き込み用SQL文
		$sql = "delete from ".$table." where site_hash = '".$data."'";
		$this->set_DB($path,$sql,50,$data,$table);
		return true;
	}

	//管理用アンテナDB更新単品
	public function set_Manage_AntennaData_Single($path,$table,$data){
		//DB書き込み用SQL文
		$sql = "update ".$table." set antenna_id = ?, title = ?, sub_title = ?, search_word = ?, comment = ?, url = ?, rss_url = ?, auther = ?, mail_address = ?, start_date = ?, pickup_pc = ?, pickup_sp = ?, pickup_rss = ?, list_max = ?, folder_log = ?, folder_dat = ?, folder_rss = ?, category_rss_max = ?, category = ?, filter = ? where site_hash = ?";
		//配列のkeyを削除（そうしないと動かない）
		$data_con = array();
		$cnt = 0;
		foreach($data as $item){
			$data_con[$cnt] = $item;
			++$cnt;
		}
		$this->set_DB($path,$sql,2,$data_con,$table);
		return true;
	}

}

?>
