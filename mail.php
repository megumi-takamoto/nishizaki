<?php
session_start();

/*-------------------------------------------------------------------

  PHPフォームメール
  2011-11-10 Ver. 1.00
  (c)style-note.net

　■フォームのnameに「;s」オプションをつけると必須項目扱いになります。
　　例) name="comment;s"
	
　■nameにemailを指定するとメールアドレスとして扱われます。
　　例) <input type="text" name="name" />

　■添付ファイルはJPG、GIF、PINGファイルのみ送信可能です。

　■入力画面(index.html)には非表示フィールドで
　　「mode」に対して「check」を必ず渡して下さい。
　　例）<input name="mode" type="hidden" value="check" />

　■確認画面(check.php)には非表示フィールドで
　　「mode」に対して「send」を必ず渡して下さい。
　　例）<input name="mode" type="hidden" value="send" />
  
　■画面の流れ
　　index.html(入力) ≫ mail.php(入力チェック) ≫
　　check.php(確認) ≫ mail.php(送信) ≫ comp.html(完了)

-------------------------------------------------------------------*/

//▼基本設定

//受け取る時のSubject（件名）
$subject='西崎流筑紫会パソコンからのお問い合わせ';

//フォームデータを受け取るメールアドレス
$to='nitibu@nishizakiryu.sakura.ne.jp,tomoko_yano0518@yahoo.co.jp';

//画像キャッシュ場所
$image_dir='cache_image';

//バウンダリー文字列
$boundary=md5(uniqid("",1));

/*-------------------------------------------------------------------

//▼入力項目

(1) 'name' => 項目名を設定
(2) 'key' =>  HTML入力フォームの「name="*****"」*****の部分を設定
(3) $form_val[0]は項目分だけ数字を増やして設定。
  
*/

$form_val[0]= array(
  'name' => '■お問合せ内容',
  'key' => 'naiyou'
);

$form_val[1]= array(
  'name' => '■ご希望 ',
  'key' => 'gokibo'
);

$form_val[2]= array(
  'name' => '■お名前',
  'key' => 'name'
);

$form_val[3]= array(
  'name' => '■お名前（フリガナ）',
  'key' => 'namekana'
);

$form_val[4]= array(
  'name' => '■メールアドレス',
  'key' => 'email'
);

$form_val[5]= array(
  'name' => '■電話番号',
  'key' => 'tel'
);

$form_val[6]= array(
  'name' => '■郵便番号',
  'key' => 'zip11'
);

$form_val[7]= array(
  'name' => '■住所',
  'key' => 'addr11'
);

$form_val[8]= array(
  'name' => '■年齢',
  'key' => 'nenrei'
);

$form_val[9]= array(
  'name' => '■内容',
  'key' => 'message'
);

/*-------------------------------------------------------------------*/



/*-------------------------------------------------------------------*/
//▼メイン
/*-------------------------------------------------------------------*/

if($_POST['mode']=='check'){
  
  //セッション初期化
  $_SESSION=array();
  
  //添付ファイル初期化
  if($handle=opendir($image_dir)) {
    while(false!==($file=readdir($handle))){
	 if($file != '.' && $file != '..' ){
       unlink($image_dir.'/'.$file);
	 }
	}
  }

  while(list($key,$val) = each($_POST)){
    $val=mb_convert_encoding($val,'EUC-JP','auto');
    $keys=str_replace(';s','',$key);
    if($keys!='submit'&&$keys!='mode'){
	 foreach($form_val as $arr){
	   if($arr['key']==$keys){
         $_SESSION['mail_format'].=$arr['name']."\n";
	   }
	 }
	 if(strpos($key,';s')!==FALSE&&$val==''){
       $_SESSION['mail_format'].='<font color="#FF0000">必須項目です</font>'."\n\n";
	   $_SESSION['error']++;
	 }
	 elseif($keys=='email'){
	   if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/',$val)){
	     $_SESSION['mail_format'].='<font color="#FF0000">メールアドレスの書式が違います</font>'."\n\n";
	     $_SESSION['error']++;
	   }else{
	     $_SESSION['email']=$val;
		 $_SESSION['mail_format'].=$val."\n\n";
	   }
	 }
	 else{
       $_SESSION['mail_format'].=htmlspecialchars($val, ENT_QUOTES)."\n\n";
     }
   }
  }
 


  
  header('Location: check.php');
  exit;
}


elseif($_POST['mode']=='send'){

  $mail_format='────────────────────────────────────'."\n";
  $mail_format.='【'.$subject.'】より以下の内容で送信されました。'."\n";
  $mail_format.='────────────────────────────────────'."\n";
  $mail_format.=$_SESSION['mail_format'];
  $mail_format.='────────────────────────────────────'."\n";
  $mail_format.=@gethostbyaddr($_SERVER['REMOTE_ADDR'])."\n";
  $mail_format.=$_SERVER['HTTP_USER_AGENT']."\n";
  $mail_format.=date("Y/m/d - H:i:s");


  $subject='=?iso-2022-jp?B?'.base64_encode(mb_convert_encoding($subject,'JIS','EUC-JP')).'?=';
  $mail_format=mb_convert_encoding(html_entity_decode($mail_format,ENT_QUOTES,'EUC-JP'),'JIS','EUC-JP');

  $header='';
  $header.='From: '.$_SESSION['email']."\n";
  $header.='Content-Type: multipart/mixed;boundary="'.$boundary.'"'."\n";
  $header.='X-Mailer: PHP/'.phpversion()."\n";
  $header.='MIME-version : 1.0'."\n";

  $body='';
  $body.='--'.$boundary."\n";
  $body.='Content-Type: text/plain;charset=ISO-2022-JP;format=followed';
  $body.='Content-Transfer-Encoding: 7bit'."\n";
  $body.="\n";
  $body.=$mail_format."\n";
  $body.= "\n";

  if($handle=opendir($image_dir)) {
    while(false!==($file=readdir($handle))){
	 if($file!='.'&&$file != '..'){

	   $filename=mb_convert_encoding($file,'JIS','auto');
	   $body.='--'.$boundary."\n";
	   $body.='Content-Type: '.$_SESSION['filetype'].';name="'.$filename.'"'."\n";
	   $body.='Content-Transfer-Encoding: base64'."\n";
	   $body.='Conteint-Disposition: attachment;filename="'.$filename.'"'."\n";
	   $body.="\n";

	   $fp=fopen($image_dir.'/'.$file, 'r') or die('error');
	   $contents=fread($fp,filesize($image_dir.'/'.$file));
	   fclose($fp);
	   $f_encoded=chunk_split(base64_encode($contents));

	   $body.=$f_encoded."\n";
	   $body.="\n";
	   
	   unlink($image_dir.'/'.$file);

	 }
	}
  }

  mail($to, $subject, $body, $header);
  
  $_SESSION = array();
  setcookie(session_name(), '', time()-42000, '/');
  session_destroy();

  header('Location: comp.html');
  exit;
}else{
 die('MODE ERROR');
}
?>