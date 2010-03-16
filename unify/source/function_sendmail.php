<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_sendmail.php 10075 2008-11-25 02:13:17Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

function sendmail($toemail, $subject, $message, $from='') {
	global $_SC, $_SCONFIG, $_SGLOBAL, $space, $_SN;
	
	$_SCONFIG['linkguide'] = 0;
	
	include template('sendmail');
	$message = ob_get_contents();
	obclean();
	
	include_once(S_ROOT.'./data/data_mail.php');
	$mail = $_SGLOBAL['mail'];
	
	//邮件头的分隔符
	$maildelimiter = $mail['maildelimiter'] == 1 ? "\r\n" : ($mail['maildelimiter'] == 2 ? "\r" : "\n");
	//收件人地址中包含用户名
	$mailusername = isset($mail['mailusername']) ? $mail['mailusername'] : 1;
	//端口
	$mail['port'] = $mail['port'] ? $mail['port'] : 25;
	$mail['mailsend'] = $mail['mailsend'] ? $mail['mailsend'] : 1;
	
	//发信者
	if($mail['mailsend'] == 3) {
		$email_from = empty($from) ? $_SCONFIG['adminemail'] : $from;
	} else {
		$email_from = $from == '' ? '=?'.$_SC['charset'].'?B?'.base64_encode($_SCONFIG['sitename'])."?= <".$_SCONFIG['adminemail'].">" : (preg_match('/^(.+?) \<(.+?)\>$/',$from, $mats) ? '=?'.$_SC['charset'].'?B?'.base64_encode($mats[1])."?= <$mats[2]>" : $from);
	}
	
	$email_to = preg_match('/^(.+?) \<(.+?)\>$/',$toemail, $mats) ? ($mailusername ? '=?'.$_SC['charset'].'?B?'.base64_encode($mats[1])."?= <$mats[2]>" : $mats[2]) : $toemail;;
	
	$email_subject = '=?'.$_SC['charset'].'?B?'.base64_encode(preg_replace("/[\r|\n]/", '', '['.$_SCONFIG['sitename'].'] '.$subject)).'?=';
	$email_message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));
	
	$headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: UCENTER_HOME ".X_VER."{$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html; charset=$_SC[charset]{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";
		
	if($mail['mailsend'] == 1) {
		if(function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
			return true;
		}
		return false;
		
	} elseif($mail['mailsend'] == 2) {

		if(!$fp = fsockopen($mail['server'], $mail['port'], $errno, $errstr, 30)) {
			runlog('SMTP', "($mail[server]:$mail[port]) CONNECT - Unable to connect to the SMTP server", 0);
			return false;
		}
	 	stream_set_blocking($fp, true);
	
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != '220') {
			runlog('SMTP', "$mail[server]:$mail[port] CONNECT - $lastmessage", 0);
			return false;
		}
	
		fputs($fp, ($mail['auth'] ? 'EHLO' : 'HELO')." uchome\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
			runlog('SMTP', "($mail[server]:$mail[port]) HELO/EHLO - $lastmessage", 0);
			return false;
		}
	
		while(1) {
			if(substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
	 			break;
	 		}
	 		$lastmessage = fgets($fp, 512);
		}
	
		if($mail['auth']) {
			fputs($fp, "AUTH LOGIN\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 334) {
				runlog('SMTP', "($mail[server]:$mail[port]) AUTH LOGIN - $lastmessage", 0);
				return false;
			}
	
			fputs($fp, base64_encode($mail['auth_username'])."\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 334) {
				runlog('SMTP', "($mail[server]:$mail[port]) USERNAME - $lastmessage", 0);
				return false;
			}
	
			fputs($fp, base64_encode($mail['auth_password'])."\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 235) {
				runlog('SMTP', "($mail[server]:$mail[port]) PASSWORD - $lastmessage", 0);
				return false;
			}
	
			$email_from = $mail['from'];
		}
	
		fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {
			fputs($fp, "MAIL FROM: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from).">\r\n");
			$lastmessage = fgets($fp, 512);
			if(substr($lastmessage, 0, 3) != 250) {
				runlog('SMTP', "($mail[server]:$mail[port]) MAIL FROM - $lastmessage", 0);
				return false;
			}
		}
	
		fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail).">\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {
			fputs($fp, "RCPT TO: <".preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail).">\r\n");
			$lastmessage = fgets($fp, 512);
			runlog('SMTP', "($mail[server]:$mail[port]) RCPT TO - $lastmessage", 0);
			return false;
		}
	
		fputs($fp, "DATA\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 354) {
			runlog('SMTP', "($mail[server]:$mail[port]) DATA - $lastmessage", 0);
			return false;
		}
	
		$headers .= 'Message-ID: <'.gmdate('YmdHs').'.'.substr(md5($email_message.microtime()), 0, 6).rand(100000, 999999).'@'.$_SERVER['HTTP_HOST'].">{$maildelimiter}";
	
		fputs($fp, "Date: ".gmdate('r')."\r\n");
		fputs($fp, "To: ".$email_to."\r\n");
		fputs($fp, "Subject: ".$email_subject."\r\n");
		fputs($fp, $headers."\r\n");
		fputs($fp, "\r\n\r\n");
		fputs($fp, "$email_message\r\n.\r\n");
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250) {
			runlog('SMTP', "($mail[server]:$mail[port]) END - $lastmessage", 0);
		}
		fputs($fp, "QUIT\r\n");
		
		return true;

	} elseif($mail['mailsend'] == 3) {

		ini_set('SMTP', $mail['server']);
		ini_set('smtp_port', $mail['port']);
		ini_set('sendmail_from', $email_from);
	
		if(function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
			return true;
		}
		return false;
	}
}

?>