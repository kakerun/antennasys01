<?php
//ログインパスワード
define("PASSWORD", "pass");

//ログイン状態
$login = false;
//パスワードフォーム表示
$pass_form = false;

//アウトプットメニュー用
$output_menu = array();
//アウトプット用文章
$output = "";
//アウトプット大量排出用
$output_large = array();

//セッション開始
session_start();
//セッション受け取り
if(isset($_SESSION["PASS"]) && $_SESSION["PASS"] != null && md5(PASSWORD) === $_SESSION["PASS"]){
    $login = true;
}else{
    //パスワードフォームからのポスト
	if(isset($_POST["action"])&&$_POST["action"]==="login"){
		if(PASSWORD === $_POST["password"]){//パスワード確認
			$_SESSION["PASS"] = md5(PASSWORD);//暗号化してセッションに保存
			header("Location:management.php");
		}else{
			session_destroy();//セッション破棄
		}
	}else{
		$pass_form = true;
	}
}

require_once('lib.php');
require_once('define.php');
require_once('db.php');
require_once('html.php');
$gdb = new GET_SET_DB;
$gdfa = new SITE_FIX;

$gata = $gdb->get_AntennaData_All($gdfa->FOLDER_DB.$gdfa->DB_ANTENNA);

//カテゴリー全部
$gcta = array();
//アンテナID全部
//$gaia = array();
foreach($gata as $item){
	$gcta[$item['antennaid']] = new GET_CATEGORY($item['antennaid'],json_decode($item['category'],true),$item['category_rss_max']);
	//$gaia[$item['antennaid']] = $item['antennaid'];
}


//メニュー内容
if(isset($_GET['p'])) {
  $menu = $_GET['p'];
}else{
  $menu = 'none';
}

//アンテナID別の設定用
if(isset($_GET['a'])) {
  $ant = $_GET['a'];
  $gdf = new SITE_EACH_DATA($gdb,$ant);
  //print $gdf->FOLDER_DAT."<br />";
}else{
  $ant = 'none';
  //$gdf = $gdfa;
}

//サイト情報変更
if(isset($_POST['change'])) {
	$ch = true;
	$leavesite = isset($_POST['leavesite']);
	$delsite = isset($_POST['delsite']);
	$owsite = isset($_POST['owsite']);
	$data_array = array(
  		'sitename' => $_POST['sitename'],
			'siteurl' => $_POST['siteurl'],
			'rssurl' => $_POST['rssurl'],
			'sitecate' => $_POST['sitecate'],
			'articleload' => $_POST['articleload'],
			'eachlink' => $_POST['eachlink'],
			'blogrollurl' => $_POST['blogrollurl'],
			'username' => $_POST['username'],
			'mailaddress' => $_POST['mailaddress'],
			'othermessage' => $_POST['othermessage'],
			'id' => $_POST['id'],
			'password' => $_POST['password'],
			'status' => $_POST['status'],
      'antennaid' => $_POST['antennaid'],
			'sitehash' => $_POST['sitehash']
			);
}else{
	$ch = false;
	$delsite = false;
	$leavesite = false;
	$owsite = false;
}

//サイト追加
if(isset($_POST['insert_new'])) {
	$insertnew = true;
  $antid = $_POST['antennaid'];
	$rssurl = $_POST['rss_url'];

}else{
	$insertnew = false;
  $antid = "";
}

//アンテナ作成
if(isset($_POST['create_antenna'])) {

	$createantenna = true;
	//カテゴリー配列のjson化
	$cate_ori = array();
	$cnt_cate = 0;
	while(isset($_POST['category_'.$cnt_cate])){
		if(($_POST['category_'.$cnt_cate]) === 'none'){
			$cate_ori['none'] = 'none';
			break;
		}
		$temp = explode("_",$_POST['category_'.$cnt_cate]);
		$cate_ori[$temp[0]] = $temp[1];
		++$cnt_cate;
	}

	$data_array = array(
  		'antennaid' => $_POST['antenna_id'],
			'title' => $_POST['title'],
			'subtitle' => $_POST['sub_title'],
			'searchword' => $_POST['search_word'],
			'comment' => $_POST['comment'],
			'url' => $_POST['url'],
			'rssurl' => $_POST['rss_url'],
			'auther' => $_POST['auther'],
			'mailaddress' => $_POST['mail_address'],
			'startdate' => $_POST['start_date'],
			'pickuppc' => $_POST['pickup_pc'],
			'pickupsp' => $_POST['pickup_sp'],
			'pickuprss' => $_POST['pickup_rss'],
			'listmax' => $_POST['list_max'],
			'folderlog' => $_POST['folder_log'],
			'folderdat' => $_POST['folder_dat'],
			'folderrss' => $_POST['folder_rss'],
			'categoryrssmax' => $_POST['category_rss_max'],
			'category' => json_encode($cate_ori),
      'filter' => $_POST['filter']
			);
}else{
	$createantenna = false;
}

//dat変更
if(isset($_POST['owdat'])) {
	$owdat = true;
	$daturl = $_POST['daturl'];
	$dattext = $_POST['dattext'];
}else{
	$owdat = false;
	$daturl = "";
	$dattext = "";
}

//複数削除
if(isset($_POST['delmlt'])){
	$delmlt = true;
	$dellist = array();
	$dellist = $_POST['deletemulti'];
}else{
	$delmlt = false;
	$dellist = array();
}

//ログイン中かどうか
if($login){
	//メニュー
  $cntm = 0;
	$output_menu[$cntm] = "<h1><a href=\"./management.php\">".$gdfa->SITE_MASTER_TITLE."管理画面</a></h1>".
      "<h2>総合アンテナ管理</h2>"."\n".
			"<a href=\"./management.php?p=leave\">承認作業</a>　\n".
			"<a href=\"./management.php?p=alter\">変更作業</a>　\n".
			"<a href=\"./management.php?p=select\">サイトDB設定</a>　\n".
			"<a href=\"./management.php?p=insert\">サイト追加</a>　\n".
			"<br /><br />\n".
      "<h2>各アンテナ設定</h2><div id=\"menu_site\">▼展開▼</div><div class=\"menu_sub\">"."\n";
	//サイト別dat
  ++$cntm;
	foreach($gata as $item){
		$output_menu[$cntm] = "<div class=\"ant_box\"><div class=\"ant_title\">".$item['title']."</div>".
		//"<a href=\"./management.php?p=msignup&a=".$item['antennaid']."\">signup.dat生成</a>　\n".
		//"<a href=\"./management.php?p=mabout&a=".$item['antennaid']."\">about.dat生成</a>　\n".
		//"<a href=\"./management.php?p=minfo&a=".$item['antennaid']."\">info.dat生成</a>　\n".
    "<a href=\"./management.php?p=select&a=".$item['antennaid']."\">DB一覧</a>\n".
		"<a href=\"./management.php?p=madfront&a=".$item['antennaid']."\">adfront.dat</a>\n".
		"<a href=\"./management.php?p=madback&a=".$item['antennaid']."\">adback.dat</a>\n".
		"</div>\n";
    ++$cntm;
	}
	$output_menu[$cntm] = "</div><div class=\"clear\"></div>\n".
      "ファイル更新関係<br />\n".
      "<a href=\"./management.php?p=indexupdate\">各indexをpkgと統一</a>　\n".
			"<br /><br />\n".
			"サイトDBアクセス状況確認<br />\n".
			"<a href=\"./management.php?p=accin\">inランク</a>　\n".
			"<a href=\"./management.php?p=accout\">outランク</a>　\n".
			"<a href=\"./management.php?p=accinsout\">返還率ランク</a>　\n".
			"<br /><br />\n".
			"アンテナ生成<br />\n".
			"<a href=\"./management.php?p=createantenna\">新規アンテナ作成</a>　\n".
			"<a href=\"./management.php?p=alterantenna\">アンテナ情報変更</a>　\n".
			"<br /><br />\n".
			"ログ関係<br />\n".
			"<a href=\"".g_FOLDER_LOG.date('Ymd').".log\">Today Out Log</a>　\n".
			"<a href=\"".g_FOLDER_LOG."error.log\">Today In  Log</a>　\n".
			"<a href=\"./management.php?p=creset\">カウントリセット</a>　\n".
			"<br /><br />\n".
			"<a href=\"./management.php?p=logout\">ログアウト</a>　\n".
			"<br /><br />\n";

	switch($menu){
		case 'pickup':
			require_once('pickup.php');
			pickup_create();
			echo "PickUp End<br />\n";
			break;
		case 'rss':
			require_once('rss.php');
			rss_create($rss);
			echo "RSS End<br />\n";
			break;
		case 'leave'://承認作業
			if($leavesite){
				//承認
				$data_array['status'] = 1;
				$data_array['articleload'] = 1;
        if($data_array['username'] === $gdfa->SITE_MASTER_AUTHER){
          $data_array['eachlink'] = 0;
        }else{
          //----一時的な措置として全部管理者登録化,あとで1にもどしてね
				  $data_array['eachlink'] = 0;
        }
				foreach($data_array as $key => $item){
					print_debug($key." ".$item);
				}

				if($gdb->set_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array)){
					$output = $output.$data_array['sitename']."を承認しました。<br /><br />\n";
					//ここでメール出したい
					$data_array['status'] = 0;
					send_mail($data_array,$gata);
				}
			}elseif($delsite){
				if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array['sitehash'])){
					$output = $output.$data_array['sitename']."を削除しました。<br /><br />\n";
				}
			}elseif($ch){
				print_debug($data_array[0]);
				if($gdb->set_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array)){
					$output = $output.$data_array['sitename']."を更新しました。<br /><br />\n";
				}
			}else{

			}
			$output_large = make_db2form($gdb,$gdfa,$gcta,0);
			break;
		case 'alter'://変更作業
			if($owsite){
				//上書きするハッシュへ
				$data_array['sitehash'] = $_POST['owsite'];
				$data_array['status'] = 1;
				$data_array['articleload'] = 1;
        if($data_array['username'] === $gdfa->SITE_MASTER_AUTHER){
          $data_array['eachlink'] = 0;
        }else{
				  $data_array['eachlink'] = 1;
        }
				if($gdb->set_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array)){
					$output = $output.$data_array['sitename']."を登録情報変更しました。<br /><br />\n";
					//ここでメール出したい
					$data_array['status'] = 2;
					send_mail($data_array,$gata);
				}
				//ついでに削除する場合
				if($delsite){
					$data_array['sitehash'] = $_POST['sitehash'];
					if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array['sitehash'])){
						$output = $output.$data_array['sitename']."を削除しました。<br /><br />\n";
					}
				}
			}elseif($delsite){
				if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array['sitehash'])){
					$output = $output.$data_array['sitename']."を削除しました。<br /><br />\n";
				}
			}elseif($ch){
				print_debug($data_array[0]);
				if($gdb->set_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array)){
					$output = $output.$data_array['sitename']."を更新しました。<br /><br />\n";
				}
			}else{

			}

			$output_large = make_db2form($gdb,$gdfa,$gcta,2);
			break;
		case 'select'://サイトDB設定
			if($delsite){
				if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array['sitehash'])){
					$output = $output.$data_array['sitename']."を削除しました。<br /><br />\n";
				}
			}elseif($ch){
				print_debug($data_array[0]);
				if($gdb->set_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$data_array)){
					$output = $output.$data_array['sitename']."を更新しました。<br /><br />\n";
				}
			}else{

			}
      $status = 1;
      if($ant !== "none"){
        //アンテナ別
        $status = 3;
      }
			$output_large = make_db2form($gdb,$gdfa,$gcta,$status,$ant);
			break;
    case 'insert'://サイト追加
			if($insertnew){
        require_once('autoloader.php');
        $feed = new SimplePie();
        $feed->set_feed_url($rssurl);
        $feed->set_cache_duration(100);//キャッシュの保持時間
        $feed->enable_cache(false);
        $feed->init();
        $items_per_feed = 1;//フィード毎の取得数
        $feed->get_item_quantity($items_per_feed);
        $x=0;
        $feed->get_item($x)->get_date('Y/n/j G:i');
        $item_data = $feed->get_item($x);
  			$feed_data = $item_data->get_feed();
        $dc_cut_feed_title = str_replace("\"", "", $feed_data->get_title());
        $dc_cut_feed_title = mb_convert_encoding($dc_cut_feed_title, 'HTML-ENTITIES', 'UTF-8');
  			$dc_cut_feed_title = mb_convert_encoding($dc_cut_feed_title, 'UTF-8' , 'HTML-ENTITIES');

        //URLのスラッシュがあるかないか
        $siteurl_fix = $feed_data->get_permalink();
        if(substr($siteurl_fix, -1) !== '/'){
          $siteurl_fix .= '/';
        }

        //DB登録
        $insert_data = array(
        	sqlite_escape_string(htmlspecialchars($dc_cut_feed_title, ENT_QUOTES)),
        	htmlspecialchars(preg_replace('/(\s|　)/','',$siteurl_fix), ENT_QUOTES),
        	htmlspecialchars(makeRandHash(8), ENT_QUOTES),
        	htmlspecialchars(preg_replace('/(\s|　)/','',$rssurl), ENT_QUOTES),
        	//htmlspecialchars($_POST['Category'], ENT_QUOTES),
        	htmlspecialchars('none', ENT_QUOTES),
        	1,0,0,0,0,0,"none",
        	htmlspecialchars($gdfa->SITE_MASTER_AUTHER, ENT_QUOTES),
        	htmlspecialchars($gdfa->MAIL_ADDRESS_MASTER, ENT_QUOTES),
        	htmlspecialchars("", ENT_QUOTES),
        	htmlspecialchars(makeRandHash(16), ENT_QUOTES),
        	htmlspecialchars(makeRandHash(16), ENT_QUOTES),
        	1,
        	$antid
        	);
        if($gdb->set_FeedData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$insert_data)){
					$output = $output.$insert_data[0]."を追加しました。<br /><br />\n";
				}
        unset($feed);
			}else{

			}
			$output_large = db_insert_site($gata,$antid);
			break;
		case 'msignup'://signup.dat生成
			if($owdat){
				$output = make_dat($gdf,$gcta[$ant],$daturl,$dattext);
			}
			$output_large = make_dat2form($gdb,$gdf,$gcta[$ant],$gdf->FOLDER_DAT.$gdf->DAT_SIGNUP);
			break;
		case 'mabout'://about.dat生成
			if($owdat){
				$output = make_dat($gdf,$gcta[$ant],$daturl,$dattext);
			}
			$output_large = make_dat2form($gdb,$gdf,$gcta[$ant],$gdf->FOLDER_DAT.$gdf->DAT_ABOUT);
			break;
		case 'minfo'://info.dat生成
			if($owdat){
				$output = make_dat($gdf,$gcta[$ant],$daturl,$dattext);
			}
			$output_large = make_dat2form($gdb,$gdf,$gcta[$ant],$gdf->FOLDER_DAT.$gdf->DAT_INFO);
			break;
		case 'madfront'://adfront.dat生成
			if($owdat){
				$output = make_dat($gdf,$gcta[$ant],$daturl,$dattext);
			}
			$output_large = make_dat2form($gdb,$gdf,$gcta[$ant],$gdf->FOLDER_DAT.$gdf->DAT_AD_FRONT);
			break;
		case 'madback'://adback.dat生成
			if($owdat){
				$output = make_dat($gdf,$gcta[$ant],$daturl,$dattext);
			}
			$output_large = make_dat2form($gdb,$gdf,$gcta[$ant],$gdf->FOLDER_DAT.$gdf->DAT_AD_BACK);
			break;
		case 'accin'://アクセス状況確認
			//サイト複数削除
			if($delmlt){
				foreach($dellist as $dl){
					if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$dl)){
						$output = $output.$dl."を削除しました。<br />\n";
					}
				}
			}
			$output_large = make_db2table($gdb,$gdfa,0);
			break;
		case 'accout'://アクセス状況確認
			//サイト複数削除
			if($delmlt){
				foreach($dellist as $dl){
					if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$dl)){
						$output = $output.$dl."を削除しました。<br />\n";
					}
				}
			}
			$output_large = make_db2table($gdb,$gdfa,1);
			break;
		case 'accinsout'://アクセス状況確認
			//サイト複数削除
			if($delmlt){
				foreach($dellist as $dl){
					if($gdb->delete_Manage_SiteData_Single($gdfa->FOLDER_DB.$gdfa->DB_LINK,$gdfa->TABLE_SITE,$dl)){
						$output = $output.$dl."を削除しました。<br />\n";
					}
				}
			}
			$output_large = make_db2table($gdb,$gdfa,2);
			break;
		case 'createantenna'://アンテナ生成
			if($createantenna){
        //配列のkeyを削除（そうしないと動かない）
    		$data_con = array();
    		$cntt = 0;
    		foreach($data_array as $item){
    			$data_con[$cntt] = $item;
    			++$cntt;
    		}
				if($gdb->set_FeedData_Single($gdfa->FOLDER_DB.$gdfa->DB_ANTENNA,$gdfa->TABLE_ANTENNA,$data_con)){
					$gdf = new SITE_EACH_DATA($gdb,$data_array['antennaid']);
          print "<br />".$data_array['antennaid']."<br />";
          print $gdf->FOLDER_MASTER_DATA.'dat/';
          print $gdf->FOLDER_DAT;
					//datコピー
					/*copy($gdf->FOLDER_MASTER_DATA.'dat/'.$gdf->DAT_ABOUT,$gdf->FOLDER_DAT.$gdf->DAT_ABOUT);
					copy($gdf->FOLDER_MASTER_DATA.'dat/'.$gdf->DAT_ALL,$gdf->FOLDER_DAT.$gdf->DAT_ALL);
					copy($gdf->FOLDER_MASTER_DATA.'dat/'.$gdf->DAT_COUNT,$gdf->FOLDER_DAT.$gdf->DAT_COUNT);
					copy($gdf->FOLDER_MASTER_DATA.'dat/'.$gdf->DAT_INFO,$gdf->FOLDER_DAT.$gdf->DAT_INFO);
					copy($gdf->FOLDER_MASTER_DATA.'dat/'.$gdf->DAT_RANKING,$gdf->FOLDER_DAT.$gdf->DAT_RANKING);
					copy($gdf->FOLDER_MASTER_DATA.'dat/'.$gdf->DAT_SIGNUP,$gdf->FOLDER_DAT.$gdf->DAT_SIGNUP);
          */
          //
          dir_copy($gdf->FOLDER_MASTER_DATA.'dat/',$data_array['folderdat']);
          dir_copy($gdf->FOLDER_MASTER_DATA.'log/',$data_array['folderlog']);
          dir_copy($gdf->FOLDER_MASTER_DATA.'rss/','./antenna/'.$data_array['antennaid'].'/rss/');
          //dir_copy($gdf->FOLDER_MASTER_DATA.'js/','./antenna/'.$data_array['antennaid'].'/js/');
          //dir_copy($gdf->FOLDER_MASTER_DATA.'css/','./antenna/'.$data_array['antennaid'].'/css/');
          //dir_copy($gdf->FOLDER_MASTER_DATA.'img/','./antenna/'.$data_array['antennaid'].'/img/');
          copy($gdf->FOLDER_MASTER_DATA.'index.php','./antenna/'.$data_array['antennaid'].'/index.php');
          copy($gdf->FOLDER_MASTER_DATA.'.htaccess','./antenna/'.$data_array['antennaid'].'/.htaccess');
          set_Antenna_ID_Self('./antenna/'.$data_array['antennaid'],$data_array['antennaid']);
					$output = $output.$data_array['title']."を作成しました。<br /><br />\n";
				}else{
					$output = $output."失敗しました。<br /><br />\n";
				}
			}
			$output = db_create_antenna();
      break;
    case 'indexupdate'://各index更新
        $cnt = 0;
				foreach($gata as $ant_l){
          copy($gdfa->FOLDER_MASTER_DATA.'index.php','./antenna/'.$ant_l['antennaid'].'/index.php');
          $output_large[$cnt] = $ant_l['title']."のindexを更新しました。<br />\n";
          ++$cnt;
          copy($gdfa->FOLDER_MASTER_DATA.'.htaccess','./antenna/'.$ant_l['antennaid'].'/.htaccess');
          $output_large[$cnt] = $ant_l['title']."の.htaccessを更新しました。<br />\n";
          ++$cnt;
				}
  			break;
  		case 'creset':
  			reset_count($gdb,$gdfa);
  			break;
		case 'logout':
			session_destroy();
			$output = "ログアウトしました<br />\n".
					"<a href=\"./management.php\">トップ画面</a><br />\n";
			break;
		default:
			break;
	}
}elseif($pass_form){
	$output = $output.'<form action="" method="post">
	<input name="password" type="password" value="" />
	<input name="action" type="submit" value="login" />
</form>';
}else{
	$output = "もう一度<br />\n".
			"<a href=\"./management.php\">トップ画面</a><br />\n";
}
?>
<?php
function tests(){
	echo "test<br />\n";
}
//サイトDB内容を更新可能なフォーム化 trueで承認作業モード
function make_db2form($gdb,$gdfa,$gcta,$status,$antid = "none"){
	$data = array();
	$cate = array();

	$cnt = 0;
	$out = array();

	if($status == 1){
		$data = $gdb->get_SiteData_All($gdfa->FOLDER_DB.$gdfa->DB_LINK);
	}elseif($status == 0){
		$data = $gdb->get_Manage_SiteData($gdfa->FOLDER_DB.$gdfa->DB_LINK,$status);
	}elseif($status == 2){
		$data = $gdb->get_Manage_SiteData($gdfa->FOLDER_DB.$gdfa->DB_LINK,$status);
	}else{
    //アンテナ別
    $data = $gdb->get_Manage_SiteData_AFilter($gdfa->FOLDER_DB.$gdfa->DB_LINK,$antid);
  }

	foreach($data as $item){
		$cate = $gcta[$item['antennaid']]->c_data();
		$out[$cnt] = '<form action="" method="post">'.
    "<a href=\"".$item['siteurl']."\">URL</a>　\n".
    $item['sitename'].'　の変更'.
		'<input type="radio" name="ret" onClick="setTextField(this.form,false)" />する'.
		'<input type="radio" name="ret" onClick="setTextField(this.form,true)" />しない　　　'.
		'<input type="checkbox" name="delsite" value="delete" disabled />削除　　';
		if($status == 0){
			//承認モード時のみ出る
			$out[$cnt] = $out[$cnt].
			'<input type="checkbox" name="leavesite" value="leave" disabled />承認<br />';
		}else{
			$out[$cnt] = $out[$cnt].'<br />';
		}
		$out[$cnt] = $out[$cnt].
		'<input name="sitename" type="text" size="24" value="'.$item['sitename'].'"disabled />'.
		'<input name="siteurl" type="text" size="24" value="'.$item['siteurl'].'"disabled />'.
		'<input name="sitehash" type="text" size="6" value="'.$item['sitehash'].'"disabled />'.
		'<input name="rssurl" type="text" size="24" value="'.$item['rssurl'].'"disabled />'.
		'<select name="sitecate" disabled>';
		//カテゴリ
		foreach($cate as $ct){
			if($ct === $item['sitecate']){
				$out[$cnt] = $out[$cnt].'<option value="'.$ct.'" selected>'.$ct.'</option>';
			}else{
				$out[$cnt] = $out[$cnt].'<option value="'.$ct.'">'.$ct.'</option>';
			}
		}
		$out[$cnt] = $out[$cnt].'</select>'.
		'<input name="articleload" type="text" size="2" value="'.$item['articleload'].'"disabled />'.
		'<input name="eachlink" type="text" size="2" value="'.$item['eachlink'].'"disabled />'.
		//'<input name="incount" type="text" value="'.$item['incount'].'" />'.
		//'<input name="outcount" type="text" value="'.$item['outcount'].'" />'.
		//'<input name="inrank" type="text" value="'.$item['inrank'].'" />'.
		//'<input name="outrank" type="text" value="'.$item['outrank'].'" />'.
		'<input name="blogrollurl" type="text" size="14" value="'.$item['blogrollurl'].'"disabled />'.
		'<input name="username" type="text" size="14" value="'.$item['username'].'"disabled />'.
		'<input name="mailaddress" type="text" size="14" value="'.$item['mailaddress'].'"disabled />'.
		'<textarea name="othermessage" type="text" row="1" cols="2" disabled>'.$item['othermessage'].'</textarea>'.
		'<input name="id" type="text" size="14" value="'.$item['id'].'"disabled />'.
		'<input name="password" type="text" size="14" value="'.$item['password'].'"disabled />'.
		'<input name="status" type="text" size="2" value="'.$item['status'].'"disabled />'.
		'<input name="antennaid" type="text" size="8" value="'.$item['antennaid'].'"disabled />';
		//移行用プルタウン
		if($status == 2){
			$site_list = $gdb->get_SiteData_All($gdfa->FOLDER_DB.$gdfa->DB_LINK);
			$out[$cnt] = $out[$cnt].'<br />上書きするサイト<select name="owsite" >';
			foreach($site_list as $li){
				//飛んで行くのはハッシュ
				$sname = $li['sitename'];
				if($li['sitehash'] === $item['sitehash']){
					$sname = $sname." - now";
				}
				$out[$cnt] = $out[$cnt].'<option value="'.$li['sitehash'].'">'.$sname.'</option>';
			}
			$out[$cnt] = $out[$cnt].'</select>';
		}


		$out[$cnt] = $out[$cnt].'<br /><input name="change" type="submit" value="変更" />'.
		'</form>'."\n";
		++$cnt;
	}
	return $out;
}

//アンテナDB内容を更新可能なフォーム化 trueで承認作業モード
function make_db2form_ant($gdb,$gdfa,$gct,$status = 1){
	$data = array();
	$cate = array();
	$cate = $gct->c_data();
	$cnt = 0;
	$out = array();

	if($status == 1){
		$data = $gdb->get_SiteData_All($gdfa->FOLDER_DB.$gdfa->DB_LINK);
	}elseif($status == 3){

	}else{
		$data = $gdb->get_Manage_SiteData($gdfa->FOLDER_DB.$gdfa->DB_LINK,$status);
	}

	foreach($data as $item){
		$out[$cnt] = '<form action="" method="post">'.
		$item['sitename'].'　の変更'.
		'<input type="radio" name="ret" onClick="setTextField(this.form,false)">する'.
		'<input type="radio" name="ret" onClick="setTextField(this.form,true)">しない　　　'.
		'<input type="checkbox" name="delsite" value="delete" disabled>削除　　';
		if($status == 0){
			//承認モード時のみ出る
			$out[$cnt] = $out[$cnt].
			'<input type="checkbox" name="leavesite" value="leave">承認<br />';
		}else{
			$out[$cnt] = $out[$cnt].'<br />';
		}
		$out[$cnt] = $out[$cnt].
		'<input name="sitename" type="text" size="24" value="'.$item['sitename'].'"disabled />'.
		'<input name="siteurl" type="text" size="24" value="'.$item['siteurl'].'"disabled />'.
		'<input name="sitehash" type="text" size="6" value="'.$item['sitehash'].'"disabled />'.
		'<input name="rssurl" type="text" size="24" value="'.$item['rssurl'].'"disabled />'.
		'<select name="sitecate" disabled>';
		//カテゴリ
		foreach($cate as $ct){
			if($ct === $item['sitecate']){
				$out[$cnt] = $out[$cnt].'<option value="'.$ct.'" selected>'.$ct.'</option>';
			}else{
				$out[$cnt] = $out[$cnt].'<option value="'.$ct.'">'.$ct.'</option>';
			}
		}
		$out[$cnt] = $out[$cnt].'</select>'.
		'<input name="articleload" type="text" size="2" value="'.$item['articleload'].'"disabled />'.
		'<input name="eachlink" type="text" size="2" value="'.$item['eachlink'].'"disabled />'.
		//'<input name="incount" type="text" value="'.$item['incount'].'" />'.
		//'<input name="outcount" type="text" value="'.$item['outcount'].'" />'.
		//'<input name="inrank" type="text" value="'.$item['inrank'].'" />'.
		//'<input name="outrank" type="text" value="'.$item['outrank'].'" />'.
		'<input name="blogrollurl" type="text" size="14" value="'.$item['blogrollurl'].'"disabled />'.
		'<input name="username" type="text" size="14" value="'.$item['username'].'"disabled />'.
		'<input name="mailaddress" type="text" size="14" value="'.$item['mailaddress'].'"disabled />'.
		'<textarea name="othermessage" type="text" disabled>'.$item['othermessage'].'</textarea>'.
		'<input name="id" type="text" size="14" value="'.$item['id'].'"disabled />'.
		'<input name="password" type="text" size="14" value="'.$item['password'].'"disabled />'.
		'<input name="status" type="text" size="14" value="'.$item['status'].'"disabled />';
		//移行用プルタウン
		if($status == 2){
			$site_list = $gdb->get_SiteData_All($gdfa->FOLDER_DB.$gdfa->DB_LINK);
			$out[$cnt] = $out[$cnt].'<br />上書きするサイト<select name="owsite" disabled>';
			foreach($site_list as $li){
				//飛んで行くのはハッシュ
				$sname = $li['sitename'];
				if($li['sitehash'] === $item['sitehash']){
					$sname = $sname." - now";
				}
				$out[$cnt] = $out[$cnt].'<option value="'.$li['sitehash'].'">'.$sname.'</option>';
			}
			$out[$cnt] = $out[$cnt].'</select>';
		}


		$out[$cnt] = $out[$cnt].'<input name="change" type="submit" value="変更" />'.
		'</form>'."\n";
		++$cnt;
	}
	return $out;
}

//サイトDAT内容を更新可能なフォーム化
function make_dat2form($gdb,$gdf,$gct,$path){
	$data = array();
	$cate = array();
	$cate = $gct->c_data();
	$cnt = 0;
	$out = array();
	$first_skip = true;//最初は飛ばしたい

	//aboutは飛ばさない
	if($path === $gdf->FOLDER_DAT.$gdf->DAT_ABOUT){
		$first_skip = false;
	//広告も読み飛ばさない
	}elseif($path === $gdf->FOLDER_DAT.$gdf->DAT_AD_FRONT){
		$first_skip = false;
	}elseif($path === $gdf->FOLDER_DAT.$gdf->DAT_AD_BACK){
		$first_skip = false;
	}

	//DAT読み込み
	$fileName = $path;
	$file = fopen($fileName, "r");

	while (!feof($file)) {
		$data[$cnt] = str_replace("\\", "" , fgets($file));
		print_debug($data[$cnt]);
		if($first_skip){
			$first_skip = false;
			continue;
		}elseif(rtrim($data[$cnt]) === "<!---->"){
			unset($data[$cnt]);
			break;
		}
		++$cnt;
		//print "$str";
	}
	fclose($file);


	$cnt = 0;
	$out[$cnt] = '<form action="" method="post">'.
	'<input name="daturl" type="text" size="30" value="'.$path.'"/>　の変更<br />'.
	'<textarea name="dattext" cols="120" rows="40" type="text">';
	++$cnt;
	foreach($data as $item){
		$out[$cnt] = $item;
		++$cnt;
	}
	$out[$cnt] = $out[$cnt].'</textarea><br />';
	++$cnt;
	$out[$cnt] = $out[$cnt].'<input name="owdat" type="submit" value="変更" />'.
	'</form>'."\n";
	++$cnt;

	return $out;
}

//サイトをDBに追加
function db_insert_site($gata,$antid){
  $out = array();
  $cnt = 0;
  $selected = "";

  $out[$cnt] = '<form action="" method="post" name="insert_site">'.
  'サイトの追加登録'.
	'<br />アンテナID<br />'."\n".
  '<select name="antennaid">'."\n".
  '<option value="">選択してください</option>'."\n";
  foreach($gata as $item){
    ++$cnt;
    if($item['antennaid'] === $antid){
      $selected = " selected";
    }
    $out[$cnt] = '<option value="'.$item['antennaid'].'"'.$selected.'>'.$item['title'].'</option>'."\n";
    $selected = "";
  }
  ++$cnt;
  $out[$cnt] = '</select>'."\n".
  '<br />サイトRSSURL<br />'."\n".
  '<input name="rss_url" type="text" size="40" value="" />'."\n".
  '<input name="insert_new" type="submit" value="サイト追加" />'."\n".
	'</form>'."\n";

  return $out;
}

//アンテナをDBに追加
function db_create_antenna(){

	$out = '<form action="" method="post" name="c_ant">'.
	'アンテナの新規作成'.
	'<input type="radio" name="ret" onClick="setTextField(this.submit,false)">する'.
	'<input type="radio" name="ret" onClick="setTextField(this.submit,true)">しない　　　'.
	$out = $out.
	'<br />アンテナID(英数字)<br />'."\n".
	'<input name="antenna_id" type="text" size="24" value="" onkeyup="copyid()" />'."\n".
	'<br />アンテナ名<br />'."\n".
	'<input name="title" type="text" size="24" value="なんとか情報アンテナ" />'."\n".
	'<br />サブタイトル<br />'."\n".
	'<input name="sub_title" type="text" size="24" value="なんとかの攻略と情報まとめ" />'."\n".
	'<br />サーチワード<br />'."\n".
	'<input name="search_word" type="text" size="24" value="アンテナ,まとめ" />'."\n".
	'<br />コメント<br />'."\n".
	'<input name="comment" type="text" size="24" value="なんとかまとめ情報、攻略を一度に閲覧できるアンテナサイトです。" />'."\n".
	'<br />URL<br />'."\n".
	'<input name="url" type="text" size="24" value="" />'."\n".
	'<br />RSSURL<br />'."\n".
	'<input name="rss_url" type="text" size="24" value="" />'."\n".
	'<br />管理者<br />'."\n".
	'<input name="auther" type="text" size="24" value="poter" />'."\n".
	'<br />メールアドレス<br />'."\n".
	'<input name="mail_address" type="text" size="24" value="example@example.com" />'."\n".
	'<br />開始日<br />'."\n".
	'<input name="start_date" type="text" size="24" value="'.date("Ymd").'" />'."\n".
	'<br />ピックアップPC<br />'."\n".
	'<input name="pickup_pc" type="text" size="24" value="8" />'."\n".
	'<br />ピックアップSP<br />'."\n".
	'<input name="pickup_sp" type="text" size="24" value="3" />'."\n".
	'<br />ピックアップRSS<br />'."\n".
	'<input name="pickup_rss" type="text" size="24" value="49" />'."\n".
	'<br />リスト最大数<br />'."\n".
	'<input name="list_max" type="text" size="24" value="50" />'."\n".
	'<br />logフォルダ<br />'."\n".
	'<input name="folder_log" type="text" size="24" value="" />'."\n".
	'<br />datフォルダ<br />'."\n".
	'<input name="folder_dat" type="text" size="24" value="" />'."\n".
	'<br />rssフォルダ<br />'."\n".
	'<input name="folder_rss" type="text" size="24" value="./rss/" />'."\n".
  '<br />フィルター単語(不要な場合はnone)<br />'."\n".
	'<input name="filter" type="text" size="24" value="/none|none|none/" />'."\n".
	'<br />カテゴリーRSS数<br />'."\n".
	'<input name="category_rss_max" type="text" size="24" value="1" />'."\n".
	'<br />カテゴリー(カテゴリ名_数字)<br />'."\n".
	'<input type="button" value="カテゴリ追加" onClick="ItemField.add();">'."\n".
	'<input type="button" value="カテゴリ削除" onClick="ItemField.remove();"><br />'."\n".
  '<input name="category_0" type="text" size="24" value="none_0" />'."\n".
	'<div id="item1">'."\n".
	'</div>'."\n";


	$out = $out.'<input name="create_antenna" type="submit" value="新規アンテナ作成" />'."\n".
	'</form>'."\n";

	return $out;
}

//サイトDBのアクセス内容を見るだけ
function make_db2table($gdb,$gdfa,$status = 0){
	$data = array();
	$cnt = 0;
	$out = array();

	$data = $gdb->get_SiteData_All($gdfa->FOLDER_DB.$gdfa->DB_LINK);

	$sortitem = 'incount';
	if($status == 0){
		$sortitem = 'incount';
	}elseif($status == 1){
		$sortitem = 'outcount';
	}elseif($status == 2){
		$sortitem = 'insout';
	}

	foreach($data as &$item){
		$item['insout'] = get_Reduction_Rate($item['incount'],$item['outcount']);
	}

	//ソート
	$siteincount = array();
	foreach($data as $key => $row){
		//$siteincount[$key] = $row['siteincount'];
		$siteincount[$key] = $row[$sortitem];
	}
	array_multisort($siteincount,SORT_DESC,$data);

	$out[$cnt] = '<form action="" method="post">'."\n";
	++$cnt;
	$out[$cnt] = '<table><tr><th>サイト名</th><th>in</th><th>out</th><th>in/out</th><tr>'."\n";
	++$cnt;
	foreach($data as $item){
		$out[$cnt] = '<tr><td>'.
		'<input type="checkbox" name="deletemulti[]" value="'.$item['sitehash'].'">'.
		'<a href="'.$item['siteurl'].'">'.$item['sitename'].'</a></td><td>'.
		$item['incount'].'</td><td>'.
		$item['outcount'].'</td><td>'.
		$item['insout'].'</td><tr>'."\n";
		++$cnt;
	}
	$out[$cnt] = '</table><br />'.'<input name="delmlt" type="submit" value="複数削除" /></form>';
	return $out;
}

//登録フォームdat生成
function make_dat($gdf,$gct,$path,$data){

	//書き込む文章
	$out = array();

$formtop = <<< FORMTOP

■申請内容<br />
  <select name="申請内容">
  <option value="新規登録">新規登録</option>
  <option value="登録情報変更">登録情報変更</option>
  </select><br />
■お名前（ハンドルネーム可）<br />
<input type="text" name="名前" /><br />
■Mail（半角）<br />
<input type="text" name="Email" />※必須<br />
■サイト名<br />
<input type="text" name="SiteName" /><br />
■サイトURL（半角）<br />
<input type="text" name="URL" />※必須<br />
■RSS URL（半角）<br />
<input  type="text" name="RSSURL" />※必須<br />

FORMTOP;

$formend = <<< FORMEND

<br />
<!--
■サイトを知ったきっかけ
<input name="知ったきっかけ[]" type="checkbox" value="友人・知人" />
友人・知人
<input name="知ったきっかけ[]" type="checkbox" value="検索エンジン" />
検索エンジン
-->

■その他<br />
<textarea name="その他"></textarea><br />
■spamチェック (1234と入力してください)<br />
<input type="text" name="SPAMKILL" />※必須<br /><br />
<input type="submit" value="送信" />
<input type="reset" value="リセット" />
</form>

FORMEND;

	$cnt = 0;

	if($path !== $gdf->FOLDER_DAT.$gdf->DAT_ABOUT){
		$out[$cnt] = '<div class="info">'."\n";
		$out[$cnt] .= $data;
	}else{
		$out[$cnt] = $data;
	}

	++$cnt;
	//signupの場合
	if($path === $gdf->FOLDER_DAT.$gdf->DAT_SIGNUP){
		$out[$cnt] = '<!---->'."\n";
    $out[$cnt] .= '<form method="post" action="'.$gdf->SITE_MASTER_URL.'mail.php">'."\n";
		$out[$cnt] .= $formtop;
		$out[$cnt] .= '<input type="hidden" name="AntennaID" value="'.$gdf->ANTENNA_ID.'">'."\n";
		++$cnt;
    //カテゴリーが複数ある場合
    if($gct->category_rss_max > 1){
      $out[$cnt] = '  ■希望カテゴリ<br />'."\n";
  		$out[$cnt] .= '  <select name="Category">'."\n";
  		$out[$cnt] .= '  <option value="">選択してください</option>'."\n";
  		foreach($gct->c_data() as $ct){
  			$out[$cnt] .= '  <option value="'.$ct.'">'.$ct.'</option>'."\n";
  		}
  		$out[$cnt] .= '  </select>'."\n";
    }else{
      $out[$cnt] = '<input type="hidden" name="Category" value="none">'."\n";
    }
		++$cnt;
		$out[$cnt] = $formend;
		++$cnt;

	}

	if($path === $gdf->FOLDER_DAT.$gdf->DAT_SIGNUP){
		$out[$cnt] = '</div>'."\n";
	}else{
	}

	//ファイルパス
	//$file_path = $directory_name."/".$file_name.".cnt";
	$file_path = $path;
	//ファイル作成
	if(touch($file_path)){
		//ファイルのパーティションの変更
		if(chmod($file_path,0644)){
		}else{exit;}
	}else{exit;}

	//ファイルをオープン
	if($filepoint = fopen($file_path,"a")){
	}else{exit;}
	//ファイルのロック
	if(flock($filepoint, LOCK_EX)){
	}else{exit;}

	//2番目の引数のファイルサイズを0にして空にする
	ftruncate($filepoint,0);
	// ファイルポインタを先頭に戻す
	fseek($filepoint, 0);
	//ファイル書き込み
	$cnt = 1;
	foreach($out as $item){
		//$item_Array = Array($i,0);
		fwrite( $filepoint,stripslashes($item)); // csvの末行に値を追加
	}
	//アンロック
	if(flock($filepoint, LOCK_UN)){
	}else{exit;}
	//ファイルを閉じる
	if(fclose($filepoint)){
	}else{exit;}
	return $path."たぶんできた。<br />";
}

//新規登録0 登録情報変更2
function send_mail($data, $gata){
  $gat = array();
  foreach($gata as $item){
      if($item['antennaid'] === $data['antennaid']){
        $gat = $item;
        break;
      }
  }

	$to      = $data['mailaddress'];
	$subject = "";
	$main_msg = "";

	if($data['status'] == 0){
		$subject = '新規登録の件 - '.$gat['title'];
		$main_msg = $data['sitename']."の登録完了しました。\r\n\r\n";
	}elseif($data['status'] == 2){
		$subject = '登録情報変更の件 - '.$gat['title'];
		$main_msg = "登録情報の変更完了しました。\r\n\r\n";
	}

	$headers = 'From: '.$gat['mailaddress']."\r\n".
		'Bcc: '.$gat['mailaddress']."\r\n".
		'Reply-To: '.$gat['mailaddress']."\r\n".
		'X-Mailer: PHP/'.phpversion();

	$message = $data['username']."様\r\n\r\n".
	"お世話になっております。\r\n".$gat['title']."です。\r\n";

	$message = $message.$main_msg."よろしくお願い致します。\r\n\r\n".
	'------------------------------------------------'."\r\n".
  $gat['title']."\r\n".$gat['auther']."\r\n".$gat['mailaddress']."\r\n".
  $gat['url'];

	mail($to, $subject, $message, $headers);
}

function log_reader(){
	//LOG読み込み
	$fileName = $gdfa->FOLDER_LOG."error.log";
	$file = fopen($fileName, "r");

	while (!feof($file)) {
		$data[$cnt] = str_replace("\\", "" , fgets($file));
		//print_debug($data[$cnt]);
		if($first_skip){
			$first_skip = false;
			continue;
		}elseif(rtrim($data[$cnt]) === "<!---->"){
			unset($data[$cnt]);
			break;
		}
		++$cnt;
		//print "$str";
	}
	fclose($file);
}

function reset_count($gdb,$gdfa){
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

//------------------------------------------------------------------------------
// ディレクトリ階層以下のコピー
// 引数: コピー元ディレクトリ、コピー先ディレクトリ
// 戻り値: 結果
function dir_copy($dir_name, $new_dir)
{
  if (!is_dir($new_dir)) {
    mkdir($new_dir,0755,true);
  }

  if (is_dir($dir_name)) {
    if ($dh = opendir($dir_name)) {
      while (($file = readdir($dh)) !== false) {
        if ($file == "." || $file == "..") {
          continue;
        }
        if (is_dir($dir_name . "/" . $file)) {
          dir_copy($dir_name . "/" . $file, $new_dir . "/" . $file);
        }
        else {
          copy($dir_name . "/" . $file, $new_dir . "/" . $file);
        }
      }
      closedir($dh);
    }
  }
  return true;
}

?>


<html>
<head>
<title><?php echo $gdfa->SITE_MASTER_TITLE; ?>管理画面</title>
<style type="text/css">
<!--
a {
  text-decoration: none;
  color: #00AAAA;
}
a:hover {
  color: #006666;
}
#menu_site {
  color: #000000;
  width: 300px;
  height: 24px;
  padding-top: 6px;
  margin-bottom: 6px;
  font-weight: bold;
  border: 1px solid #999999;
  background-color: #BBFFBB;
  text-align: center;
  display: block;
}
.ant_box {
  float: left;
  width: 300px;
  height: 60px;
  border: 1px solid #999999;
  margin-right: 6px;
  margin-bottom: 6px;
  padding: 6px;
  display: block;
}
.ant_box a {
  height: 24px;
  padding: 4px;
  background-color: #FF9999;
  border: 1px solid #FF3333;
  color: #000000;
  text-decoration: none;
}
.ant_box a:hover {
  background-color: #FF6666;
}
.ant_title {
  color: #000000;
  margin-top: 8px;
  margin-bottom: 8px;
  font-weight: bold;
}
.clear {
  clear: both;
}
-->
</style>
<script type="text/javascript" src="http://example.com/library/jquery-1.9.1.min.js"></script>
<script language="JavaScript"><!--
$(function(){
    $('.menu_sub').hide();
    //
    $('#menu_site').click(function(e){
        $('+div.menu_sub',this).slideToggle();
    });
});
function setTextField(fObj,flag){
	fObj.sitename.disabled = flag;
	fObj.siteurl.disabled = flag;
  fObj.sitehash.disabled = flag;
	fObj.rssurl.disabled = flag;
	fObj.sitecate.disabled = flag;
	fObj.articleload.disabled = flag;
	fObj.eachlink.disabled = flag;
	fObj.blogrollurl.disabled = flag;
	fObj.username.disabled = flag;
	fObj.mailaddress.disabled = flag;
	fObj.othermessage.disabled = flag;
	fObj.id.disabled = flag;
	fObj.password.disabled = flag;
	fObj.status.disabled = flag;
  fObj.antennaid.disabled = flag;
  fObj.delsite.disabled = flag;
  fObj.leavesite.disabled = flag;
  fObj.owsite.disabled = flag;
}
function copyid() {
  document.c_ant.url.value = 'http://' + document.c_ant.antenna_id.value + '.example.com/';
  document.c_ant.rss_url.value = 'http://' + document.c_ant.antenna_id.value + '.example.com/index/';
  document.c_ant.folder_dat.value = '../../antenna/example/' + document.c_ant.antenna_id.value + '/dat/';
  document.c_ant.folder_log.value = '../../antenna/example/' + document.c_ant.antenna_id.value + '/log/';
}
var ItemField = {
    currentNumber : 0,
    itemTemplate : '<input name="category___count__" type="text" size="24" value="none" />',
    add : function () {
        this.currentNumber++;

        var field = document.getElementById('item' + this.currentNumber);

        var newItem = this.itemTemplate.replace(/__count__/mg, this.currentNumber);
        field.innerHTML = newItem;

        var nextNumber = this.currentNumber + 1;
        var new_area = document.createElement("div");
        new_area.setAttribute("id", "item" + nextNumber);
        field.appendChild(new_area);
    },
    remove : function () {
        if ( this.currentNumber == 1 ) { return; }

        var field = document.getElementById('item' + this.currentNumber);
        field.removeChild(field.lastChild);
        field.innerHTML = '';

        this.currentNumber--;
    }
}
// --></script>
</head>

<body>

<?php
//アウトプット
foreach($output_menu as $item){
	echo $item;
}
//echo $output_menu;
echo $output;
foreach($output_large as $item){
	echo $item;
}
?>

</body>
</html>
