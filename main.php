<?php
/*
Plugin Name: spring mail form
Plugin URI: http://www.ultaimai.org/
Description: お問い合せフォームのプラグイン
Author: tailtension
Version: 0.1
*/

final class OriginalMailField{


  public static $uri ;
  public static $dir ;

  public function __construct(){
    if (session_status() != PHP_SESSION_ACTIVE)	@session_start();

    date_default_timezone_set('Asia/Tokyo'); 
    self::$uri = get_template_directory_uri(); 
    self::$dir = get_template_directory();
    
    //ショートコード		
    add_shortcode('mf_main_container',function(){
      // 本体固定ページ			
      $this->mail_container();
    });


  } //construct end 







  public function mail_container(){  //short code
  /*
		form rooting  customer side
  */
   


    if(!empty($_POST['onamae']) && !empty($_POST['token']) && $_POST['token']==$_SESSION['mf']['token'] && $_POST['sub']=='確認へ'){
      //確認画面
      echo "<h3>確認して下さい</h3>";

      foreach ($_POST as $key => $value) {
        if($key == 'sub') break;
        $post[$key] = self::h($value);
        echo '<p>', $key ,' : ', $value ,'</p>';
      }
      $_SESSION['mf']['toiawase_post'] = $post;
      // CSRF対策
      $_SESSION['mf']['csrf'] = self::token();
      echo '
        <form enctype="multipart/form-data">
        <input type="hidden" name="token" value="'.$_SESSION['mf']['csrf'].'">
        <p> <input type="submit" name="submit" value="送信">
        </form>
      ';

    }elseif( !empty($_SESSION['mf']['toiawase_post']) && !empty($_GET['token']) && $_GET['token'] == $_SESSION['mf']['csrf'] ){
      // 送信完了
      $post = $_SESSION['mf']['toiawase_post'];

      $admin_email = get_option('admin_email');
      $headers  = 'From: チャットコミュニケーション <'.$admin_email.'>' . "\r\n";
    	$subject = 'お問い合せを受付ました';
      $body = "以下の内容で登録しました。 \n";
      $to = [$post['email'] , $admin_email];

      foreach ($post as $key => $value) {

        $body .= $key .' : '. $value ."\r\n";
      }

      wp_mail( $to , $subject, $body, $headers );

      $_SESSION['mf'] = array();
      echo "<p>", $subject ,"<pre>", $body ,"</pre>";

    }else{
      
     // CSRF対策
     $_SESSION['mf']['token'] = self::token();
     $html = '
    <form method="post" action="/library/inquiry.html" enctype="multipart/form-data">
    <p><input type="text" name="onamae" class="onamae" size="33" maxlength="33" value="" placeholder="お名前 your name" require>
    <p> <input type="email" name="email" class="email" size="33" value="" placeholder="メールアドレス email" require >
    <p> <textarea name="comment" cols="33" rows="5" placeholder="お問い合わせ内容"></textarea>
    <p> <input type="submit" name="sub" value="確認へ">
     <input type="hidden" name="token" value="'.$_SESSION['mf']['token'].'">
    </form><!-- contaienr -->	
    ';
      echo $html;
    }
    

  } // mail_container end

  public static function token($length = 20){  	
    return substr(str_shuffle('1234567890QWERTYUIOPLKJHGFDSAZXCVBNMabcdefghijklmnopqrstuvwxyz~!@#$%^&*'), 0, $length);
  }	
  public static function h($p){
    $p = htmlspecialchars($p);
    $p = str_replace( ' ','' , $p ); //半角空白除去
    $p = str_replace( ',','、' , $p ); //半角カンマ置換
    return $p;
  }	
} //class end


new OriginalMailField();
  
