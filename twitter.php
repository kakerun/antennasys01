<?php
//tweet_create();

function tweet_create($post_summary)
{

require_once("library/twitteroauth.php");
 
// Consumer keyの値
$consumer_key = "xxxx";
// Consumer secretの値
$consumer_secret = "xxxx";
 
// Access Tokenの値
$access_token = "xxxx-xxxx";
// Access Token Secretの値
$access_token_secret = "xxxx"; 

// OAuthオブジェクト生成
$to = new TwitterOAuth(
        $consumer_key,
        $consumer_secret,
        $access_token,
        $access_token_secret);
 
// TwitterへPOSTする。パラメーターは配列に格納する
$req = $to->OAuthRequest(
    "https://api.twitter.com/1.1/statuses/update.json",
    "POST",
    array("status"=>$post_summary)
        );

//リフォロー処理 
$limit_time = date('H');
$limit_time_m = date('i');

$refollow_flag = false;
//8時間に1回
if($limit_time == 22 || $limit_time == 6 || $limit_time == 14){
	if($limit_time_m < 9){
		$refollow_flag = true;
	}
}

//trueの時のみリフォロー実行
if($refollow_flag){
//if($limit_time == 25){
	# 自分のユーザーIDを取得
	$result = $to->get('account/verify_credentials');
	$myId = $result->id_str;
	
	# フォロー返ししないIDをセット
	$exceptions = array('11111111', '2222222222');
	
	# フォロワーのIDを取得
	$cursor = '-1';
	$followers_ids = array();
	do{
		$result = $to->get('followers/ids', array('user_id' => $myId, 'cursor' => $cursor, 'stringify_ids' => 'true'));
		$followers_ids = array_merge($followers_ids, $result->ids);
		if(!isset($result->error)){
			$cursor = $result->next_cursor_str;
		}else{
			$cursor = '0';
		}
	}while($cursor !== '0');
	
	# フォローユーザーのIDを取得
	$cursor = '-1';
	$friends_ids = array();
	do{
		$result = $to->get('friends/ids', array('user_id' => $myId, 'cursor' => $cursor, 'stringify_ids' => 'true'));
		$friends_ids = array_merge($friends_ids, $result->ids);
		if(!isset($result->error)){
			$cursor = $result->next_cursor_str;
		}else{
			$cursor = '0';
		}
	}while($cursor !== '0');
	
	# フォロー返しするIDをセット
	$ids = array_diff($followers_ids, $friends_ids, $exceptions);
	
	# フォロー返し
	foreach($ids as $value){
		$to->post('friendships/create', array('user_id' => $value));
	}
	
	# フォロー解除 4日に1回
	$limit_date = date('d');
	if($limit_date % 4 == 0 && $limit_time == 6){
		# フォロー解除するIDをセット
		$idd = array_diff($friends_ids, $followers_ids, $exceptions);
		
		# フォロー解除 1回50まで
		$i = 0;
		foreach($idd as $value){
			$to->post('friendships/destroy', array('user_id' => $value));
			$i += 1;
			if($i >= 50){
				break;
			}
		}
	}
}

}
?>