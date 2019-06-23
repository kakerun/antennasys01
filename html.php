<?php
require_once('define.php');
//HTML部分生成用クラス
class MAKE_HTML{
	//ランキング
	private $rank_num;

	//パラメータ
	private $param_c = "index/";// ?c=
	private $param_d = "index/";// ?d=
	private $next_c = "/";// &c=
	private $next_p = "/";// &p=

	//出力listベース(データ配列,用途{標準用->0 PickUp->1 ランキング->2 RSS用->3},li class=hv)
	function model_list_html($item,$use,$cls = 'hv'){
		global $ua_smartphone;
		//class別
		$css1 = "st1";
		$css2 = "st2";
		$css3 = "st3";
		$rank_item = "";
		//hv以外だったらRSS用ライト
		if($cls !== 'hv'){
			$css1 = "st1_rss";
			$css2 = "st2_rss";
			$css3 = "st3_rss";
		}
		//ランキングはランキング用に
		if($use == 2){
			$rank_item = $this->rank_num."位";
		}

		//Baseに書き込んでいく
		$base = '<li class="'.$cls.'"><span class="graph" style="width:';
		$base = $base.$this->click_num($item['clickcount']).
			'px;"></span><a href="'.
			$item['articleurl'].
			"\"target=\"_blank\" onclick=\"ccnt('".$item['sitehash']."','".
			$item['articlehash']."','".date('Ymd', strtotime($item['timestamp']))."');\">\n".
			"<div class=\"".$css1."\">".
			$rank_item."<img src=\"http://favicon.st-hatena.com/?url=".
			$item['articleurl'].
			"\" / class=\"img-polaroid\" alt=\"\"  width=\"16\" height=\"16\"> ".
			$item['articlename'].
			" </div>".
			"<span class=\"".$css2."\">".
			$item['sitename'].
			"</span>".
			"<span class=\"".$css3."\">";

		//スマホはじゃなければ
		if($ua_smartphone == false){
			//標準かその他かで分ける
			if($use == 0){
				//時間
				$base = $base.date('H:i', strtotime($item['timestamp']));
			}else{
				//buzzとhatena
				/*$base = $base."<img src=\"http://tools.tweetbuzz.jp/imgcount?url=".
					$item['articleurl']."\" />".
					"<img src=\"http://b.hatena.ne.jp/entry/image/".
					$item['articleurl']."\" />";*/
			}
		}

		$base = $base."</span></a></li>\n";
		return $base;
	}

	//ジャンルメニュー表示
	public function menu_list($category,$cate,SITE_EACH_DATA $gdf)
	{
		$pc = $gdf->SITE_URL.$this->param_c;
		//メニュー
		if($cate === "rank"){
			echo "<li class=rank_now><a href=\"".$pc."rank\"><div>ランキング</div></a></li>\n";
		}else{
			echo "<li class=rank><a href=\"".$pc."rank\"><div>ランキング</div></a></li>\n";
		}
		for($i = 0; $i < count($category); $i++){
			if($cate === "list" || $cate === "info" || $cate === "signup" || $cate === "rank"){
				echo "<li><a href=\"".$pc."$i\"><div>$category[$i]</div></a></li>\n";
			}elseif($cate == $i){
				echo "<li class=now><a href=\"".$pc."$i\"><div>$category[$i]</div></a></li>\n";
			}else{
				echo "<li><a href=\"".$pc."$i\"><div>$category[$i]</div></a></li>\n";
			}
		}
	}

	//dat化ページ表示
	public function dat_view($path){
		$fileName = $path;
		$file = fopen($fileName, "r");

		while (!feof($file)) {
		  $str = fgets($file);
		  print "$str";
		}
		fclose($file);
	}

	//dat化ページ表示(infoとsignup用)
	public function dat_view_ex($path,SITE_EACH_DATA $gdf){
		$fileName = $path;
		$file = fopen($fileName, "r");

		$tags = array("%url%","%title%","%titletrim%","%aid%");
		$conb = array($gdf->SITE_URL,$gdf->SITE_TITLE,mb_substr($gdf->SITE_TITLE, 0, mb_strlen($gdf->SITE_TITLE) - 12),$gdf->ANTENNA_ID);

		while (!feof($file)) {
		  $str = fgets($file);
			//変換
			$str = str_replace($tags,$conb,$str);
		  print "$str";
		}
		fclose($file);
	}

	//PickUp画像
	public function list_pickup_img($pickup,SITE_EACH_DATA $gdf){
		$i = 0;
		//$array = array(0,1,2,3,4,5,6,7,8);
		//$rnd = 0;
		//ランダム値0～2
		$favoimg = array();
		for($i = 0; $i < 4; $i++){
			$ar = array();
			$ar = range($i*3,$i*3+2);
			shuffle($ar);
			array_push($favoimg,$ar[0]);
		}

		$cnt = 0;
		foreach($pickup as $item){
			/*if($cnt == 0){
				//echo '<div class="top_imgfavo_first"><div>';
				echo '<div class="top_imgfavo"><div>';
			}else{
				echo '<div class="top_imgfavo"><div>';
			}*/
			//簡略化
			$T_article_url = $pickup[$favoimg[$cnt]]['articleurl'];//記事url
			$T_article_hash = $pickup[$favoimg[$cnt]]['articlehash'];//記事ハッシュ
			$T_article_name = $pickup[$favoimg[$cnt]]['articlename'];//記事名
			$T_site_hash = $pickup[$favoimg[$cnt]]['sitehash'];//サイトハッシュ
			$T_time_stamp = $pickup[$favoimg[$cnt]]['timestamp'];//時間
			$T_img_url = $pickup[$favoimg[$cnt]]['imgurl'];//画像url
			//クリックカウント用
			$ccnt_item = $T_site_hash."','".$T_article_hash."','".date('Ymd', strtotime($T_time_stamp));
			//リンク用
			$url_a = '<a href="'.$T_article_url."\" target=\"_blank\" onclick=\"ccnt('".$ccnt_item."');\">";
			//画像
			$url_img = $gdf->IMG_NOIMG;
			if($T_img_url === '' || str_replace('"', '', $T_img_url) === ' '){
			}else{
				$img = htmlspecialchars_decode($T_img_url);
				if(preg_match('/(.jpg)|(.jpeg)|(.png)|(.gif)/',$img)){
					$url_img = $img;
				}
			}

			echo '<div class="top_imgfavo" style="background-image:url('."'".$url_img."'".'); background-size:cover;">';
			echo $url_a;
			echo '<div></div></a><div class="black_win">';
			echo $url_a;
			echo mb_strimwidth($T_article_name, 0, 88, '',utf8);
			echo '</a></div></div>';

			/*echo '<img src="';
			echo $url_img;
			echo '" width="213" height="180" alt="" /></a></div>';*/
			++$cnt;
			//
			if($cnt == 4){ break; }
		}
	}

	//PickUp
	public function list_pickup($feeds,$tline,SITE_EACH_DATA $gdf)
	//$feeds_title,$feeds_url,$feeds_sitename,$feeds_date,$feeds_countfile,$rss)
	{
		$cnt = 0;
		$cnt_last = count($feeds);//カウント
		$pickup_max = $gdf->PICKUP_PC;
		$pickup_rss = 0;

		global $ua_smartphone;
		if($ua_smartphone == true){
			$pickup_max = $gdf->PICKUP_SP;
		}
		if($tline != 0){
			$pickup_max = $gdf->PICKUP_RSS;
			$pickup_rss = $gdf->PICKUP_SP;
		}
		foreach($feeds as $item){
			if($item['articlename'] === ""){
				//break;
			//}elseif($cnt == $pickup_rss && $rss !== 'none'){
				continue;
			}elseif($item['rssnum'] === $tline){
				echo $this->model_list_html($item,1,'rss_light');
			}else{
				echo $this->model_list_html($item,1);
			}

			++$cnt;
			//脱出
			if($cnt > $pickup_max || $cnt > $cnt_last){
				break;
			}
		}
	}

	//ランキング
	public function list_rank($feeds)
	{
		$cnt = 0;

		foreach($feeds as $item){
			//ランキング数値
			$this->rank_num = $cnt + 1;
			//タイトル不明は削除
			if($item['articlename'] === ""){++$cnt;continue;}
			//タイトル
			echo $this->model_list_html($item,2);
			++$cnt;
		}
	}

	//標準出力
	public function list_view($feeds)
	{
		$cnt = 0;

		$item_print = array();//表示用配列
		$item_duplicate = array();//重複有り判定
		foreach($feeds as $item){
			//日表示
			if($item['sitehash'] === 'dayline'){
				array_push($item_print, "<li class=\"hv\">".
					"<div class=\"dayline\">".
					$item['timestamp'].
					"</div></li>\n");
			}else{
				//echo "<li class=\"hv\"><a href=\"";
				array_push($item_print, $this->model_list_html($item,0));
			}
			$cnt_dps = 0;
			//重複判定ちゃん
			foreach($feeds as $title_dpc){
				if($cnt_dps == $cnt){
					array_push($item_duplicate,'none');
					break;
				}elseif($title_dpc['articlename'] === $item['articlename']){
					array_push($item_duplicate,$cnt_dps);
					if($item_duplicate[$cnt_dps] === 'none'){
						$item_duplicate[$cnt_dps] = 'par-'.$cnt;
					}else{
						$temp = $item_duplicate[$cnt_dps];
						$item_duplicate[$cnt_dps] = $temp.'-'.$cnt;
					}
					break;
				}
				++$cnt_dps;
			}
			++$cnt;
		}
		$cnt_dps = 0;
		$item_exclude = array();
		//整列ちゃん
		foreach($item_print as $item){
			if($item_duplicate[$cnt_dps] === 'none' && array_search($cnt_dps,$item_exclude) === false){
				echo $item;
			}elseif(substr($item_duplicate[$cnt_dps], 0, 3) === 'par'){
				echo '<div class="accordion_head"><img src="http://example.com/img/down.png" width="16" height="16" /> '.
					$feeds[$cnt_dps]['articlename']."</div><div>\n";
				echo $item;
				//重複の表示とあとで表示させない用
				$array_dps = array();
				$array_dps = explode('-',$item_duplicate[$cnt_dps]);
				$cnt_dd = 0;
				foreach($array_dps as $dps){
					if($cnt_dd == 0){
					}else{
						echo $item_print[$dps];
						array_push($item_exclude,$dps);
					}
					$cnt_dd++;
				}
				echo '</div>';
			}
			++$cnt_dps;
		}
	}

	//サイト一覧
	public function list_site($feeds)
	{
		$cnt = 0;
		foreach($feeds as $item){
			echo "<li class=\"hv\"><a href=\"";
			echo $item['siteurl'];
			echo "\"target=\"_blank\" onclick=\"ccnt('".$item['sitehash']."','link','link');\">\n";
			echo "<div class=\"st91\"><img src=\"http://favicon.st-hatena.com/?url=";
			echo $item['siteurl'];
			echo "\" / class=\"img-polaroid\" alt=\"\"  width=\"16\" height=\"16\"> ";
			echo $item['sitename'];
			echo " </div><span class=\"st92\">";
			echo $item['siteurl'];
			echo "</span></a></li>\n";
		$cnt++;
		}
	}

	//ナビ
	public function navi(SITE_EACH_DATA $gdf,$date,$page,$count,$max,$cate){
		//パラメータ
		$pd = $gdf->SITE_URL.$this->param_d;
		$nc = $this->next_c;
		$np = $this->next_p;
		//日
		$date_yesterday = date('m/d', strtotime('-1 day' . $date));
		$date_tomorrow = date('m/d', strtotime('+1 day' . $date));
		$link_yesterday = date('Ymd', strtotime('-1 day' . $date));
		$link_tomorrow = date('Ymd', strtotime('+1 day' . $date));
		//ページ数
		$cnt_pagelist = ceil($count / $max);//切り上げ
		//前の日
		if($cate == 99 && $link_yesterday > $gdf->START_DATE){
			echo "<a href=\"".$pd.$link_yesterday."\">前日(".$date_yesterday.")</a>\n";
		}elseif($link_yesterday > $gdf->START_DATE){
			echo "<a href=\"".$pd.$link_yesterday.$nc.$cate."\">前日(".$date_yesterday.")</a>\n";
		}
		//ナビ
		$page_now = $page;

		if($page > 3){
			$page = $page - 3;
		}elseif($page > 2){
			$page = $page - 2;
		}elseif($page > 1){
			$page = $page - 1;
		}

		for($i = 1 ; $i <= 7 ; $i++){
			//7個制限
			if($cnt_pagelist < $page){
				//次の日
				if($date != date('Ymd')){
					if($cate == 99){
						echo "<a href=\"".$pd.$link_tomorrow."\">翌日(".$date_tomorrow.")</a>\n";
					}else{
						echo "<a href=\"".$pd.$link_tomorrow.$nc.$cate."\">翌日(".$date_tomorrow.")</a>\n";
					}
				}
				break;
			}
			//7個目最後は...
			if($i == 7){
				if($page_now == $page){
					echo "<span>".$page."</span>";
				}elseif($cate == 99){
					echo "<a href=\"".$pd.$date.$np.$page."\">"."…"."</a>\n";
					echo "<a href=\"".$pd.$date.$np.$cnt_pagelist."\">"."&gt;&gt;"."</a>\n";
				}else{
					echo "<a href=\"".$pd.$date.$nc.$cate.$np.$page."\">"."…"."</a>\n";
					echo "<a href=\"".$pd.$date.$nc.$cate.$np.$cnt_pagelist."\">"."&gt;&gt;"."</a>\n";
				}
				//次の日
				if($date != date('Ymd')){
					if($cate == 99){
						echo "<a href=\"".$pd.$link_tomorrow."\">翌日(".$date_tomorrow.")</a>\n";
					}else{
						echo "<a href=\"".$pd.$link_tomorrow.$nc.$cate."\">翌日(".$date_tomorrow.")</a>\n";
					}
				}
				break;
			}else{
				//最初が1以外だと...
				$date_temp = $date;
				if($i == 1 && $page != 1){
					if($page_now == $page){
						echo "<span>".$page."</span>";
					}elseif($cate == 99){
						echo "<a href=\"".$pd.$date.$np."1"."\">"."&lt;&lt;"."</a>\n";
						echo "<a href=\"".$pd.$date.$np.$page."\">"."…"."</a>\n";
					}else{
						echo "<a href=\"".$pd.$date.$nc.$cate.$np."1"."\">"."&lt;&lt;"."</a>\n";
						echo "<a href=\"".$pd.$date.$nc.$cate.$np.$page."\">"."…"."</a>\n";
					}
				}else{
					if($page_now == $page){
						echo "<span>".$page."</span>";
					}elseif($cate == 99){
						echo "<a href=\"".$pd.$date.$np.$page."\">".$page."</a>\n";
					}else{
						echo "<a href=\"".$pd.$date.$nc.$cate.$np.$page."\">".$page."</a>\n";
					}
				}
				$date = $date_temp;
			}

			++$page;
		}
	}

	//フッターRSS
	public function footer_rss(SITE_EACH_DATA $gdf,$category){
		$cnt = 0;
		echo '<li><a href="'.$gdf->SITE_URL.'index.xml">総合</a></li>'."\n";
		foreach($category as $item){
			echo '<li><a href="'.$gdf->SITE_URL.'index_'.$cnt.'.xml">'.$item.'</a></li>'."\n";
			++$cnt;
		}
	}

	//クリックカウント数を返す
	function click_num($ccnt){
		//widthに合わせる
		$ccnt = $ccnt * 10;
		if($ccnt >= 500){
			$ccnt = 500;
		}
		return $ccnt;
	}

	//ソーシャルボタン //シェア対象のURLアドレスを指定する
	public function sosial_button(SITE_EACH_DATA $gdf,$url){
		$data = '<div class="social-area-syncer">'.
			'<ul class="social-button-syncer">'.
			//Twitter
			'<li class="sc-tw"><a data-url="'.$url.'" href="https://twitter.com/share" class="twitter-share-button" data-lang="ja" data-count="vertical" data-dnt="true">ツイート</a></li>'.
			//Facebook
			'<li class="sc-fb"><div class="fb-like" data-href="'.$url.'" data-layout="box_count" data-action="like" data-show-faces="true" data-share="false"></div></li>'.
			//Google+
			'<li><div data-href="'.$url.'" class="g-plusone" data-size="tall"></div></li>'.
			//はてなブックマーク
			'<li><a href="http://b.hatena.ne.jp/entry/'.$url.'" class="hatena-bookmark-button" data-hatena-bookmark-layout="vertical-balloon" data-hatena-bookmark-lang="ja" title="このエントリーをはてなブックマークに追加"><img src="https://b.st-hatena.com/images/entry-button/button-only@2x.png" alt="このエントリーをはてなブックマークに追加" width="20" height="20" style="border:none;" /></a></li>'.
			//pocket
			'<li><a data-save-url="'.$url.'" data-pocket-label="pocket" data-pocket-count="vertical" class="pocket-btn" data-lang="en"></a></li>'.
			//LINE
			'<li class="sc-li"><a href="http://line.me/R/msg/text/?'.rawurlencode($url).'"><img src="'.$gdf->SITE_MASTER_URL.'img/linebutton_36x60.png" width="36" height="60" alt="LINEに送る" class="sc-li-img"></a></li>'.
			'</ul>'.
			//Facebook用
			'<div id="fb-root"></div>'.
			'</div>';
		return $data;
	}
}

//HTML部分生成アプリ用クラス
class MAKE_APP extends MAKE_HTML{
	//そのうち作ろうかな
}

?>
