<?php
session_start();

/*-------------------------------------------------------------------

  PHP�ե�����᡼��
  2011-11-10 Ver. 1.00
  (c)style-note.net

�����ե������name�ˡ�;s�ץ��ץ�����Ĥ����ɬ�ܹ��ܰ����ˤʤ�ޤ���
������) name="comment;s"
	
����name��email����ꤹ��ȥ᡼�륢�ɥ쥹�Ȥ��ư����ޤ���
������) <input type="text" name="name" />

����ź�եե������JPG��GIF��PING�ե�����Τ�������ǽ�Ǥ���

�������ϲ���(index.html)�ˤ���ɽ���ե�����ɤ�
������mode�פ��Ф��ơ�check�פ�ɬ���Ϥ��Ʋ�������
�������<input name="mode" type="hidden" value="check" />

������ǧ����(check.php)�ˤ���ɽ���ե�����ɤ�
������mode�פ��Ф��ơ�send�פ�ɬ���Ϥ��Ʋ�������
�������<input name="mode" type="hidden" value="send" />
  
�������̤�ή��
����index.html(����) �� mail.php(���ϥ����å�) ��
����check.php(��ǧ) �� mail.php(����) �� comp.html(��λ)

-------------------------------------------------------------------*/

//����������

//����������Subject�ʷ�̾��
$subject='����ή�޻��ѥ����󤫤�Τ��䤤��碌';

//�ե�����ǡ�����������᡼�륢�ɥ쥹
$to='nitibu@nishizakiryu.sakura.ne.jp,tomoko_yano0518@yahoo.co.jp';

//��������å�����
$image_dir='cache_image';

//�Х�����꡼ʸ����
$boundary=md5(uniqid("",1));

/*-------------------------------------------------------------------

//�����Ϲ���

(1) 'name' => ����̾������
(2) 'key' =>  HTML���ϥե�����Ρ�name="*****"��*****����ʬ������
(3) $form_val[0]�Ϲ���ʬ�������������䤷�����ꡣ
  
*/

$form_val[0]= array(
  'name' => '������礻����',
  'key' => 'naiyou'
);

$form_val[1]= array(
  'name' => '������˾ ',
  'key' => 'gokibo'
);

$form_val[2]= array(
  'name' => '����̾��',
  'key' => 'name'
);

$form_val[3]= array(
  'name' => '����̾���ʥեꥬ�ʡ�',
  'key' => 'namekana'
);

$form_val[4]= array(
  'name' => '���᡼�륢�ɥ쥹',
  'key' => 'email'
);

$form_val[5]= array(
  'name' => '�������ֹ�',
  'key' => 'tel'
);

$form_val[6]= array(
  'name' => '��͹���ֹ�',
  'key' => 'zip11'
);

$form_val[7]= array(
  'name' => '������',
  'key' => 'addr11'
);

$form_val[8]= array(
  'name' => '��ǯ��',
  'key' => 'nenrei'
);

$form_val[9]= array(
  'name' => '������',
  'key' => 'message'
);

/*-------------------------------------------------------------------*/



/*-------------------------------------------------------------------*/
//���ᥤ��
/*-------------------------------------------------------------------*/

if($_POST['mode']=='check'){
  
  //���å��������
  $_SESSION=array();
  
  //ź�եե���������
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
       $_SESSION['mail_format'].='<font color="#FF0000">ɬ�ܹ��ܤǤ�</font>'."\n\n";
	   $_SESSION['error']++;
	 }
	 elseif($keys=='email'){
	   if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/',$val)){
	     $_SESSION['mail_format'].='<font color="#FF0000">�᡼�륢�ɥ쥹�ν񼰤��㤤�ޤ�</font>'."\n\n";
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

  $mail_format='������������������������������������������������������������������������'."\n";
  $mail_format.='��'.$subject.'�ۤ��ʲ������Ƥ���������ޤ�����'."\n";
  $mail_format.='������������������������������������������������������������������������'."\n";
  $mail_format.=$_SESSION['mail_format'];
  $mail_format.='������������������������������������������������������������������������'."\n";
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