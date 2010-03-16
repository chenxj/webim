<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: class_xmlrpc.php 12844 2009-07-23 04:27:17Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

class xmlrpc {

	var $xmlserver;
	var $callback;
	var $xmlmessage;
	var $db;
	var $timestamp;
	var $member;

	function __construct() {
		global $_SGLOBAL,$_SC;
		
		$this->callback = $this->xmlrpcApi();
		$this->xmlmessage = new stdClass();
		$this->siteUrl = getsiteurl();
		$this->db = $_SGLOBAL['db'];
		$this->charset = $_SC['charset'];
		$this->timestamp = $_SGLOBAL['timestamp'];
	}

	function xmlrpc() {
		$this->__construct();
	}

	function xmlrpcSet() {
		return new xmlrpc();
	}

	function xmlrpcServer() {
		if (phpversion() < '4.3.0') {
			$data = empty($GLOBALS['HTTP_RAW_POST_DATA'])?'':$GLOBALS['HTTP_RAW_POST_DATA'];
		} else {
			$data = file_get_contents("php://input");
		}

		if(!$data) {
			$this->sendFault(1, 'Invalid Method Call');
		} else {
			$data = addslashes($data);
		}
		$this->xmlmessage->structTypes = array();
		$this->xmlmessage->structs = array();
		$this->xmlmessage->struct_name = array();
		if ($this->xmlrpcParse($data)) {
			$result = $this->xmlrpcCall($this->xmlmessage->methodname, $this->xmlmessage->params);
			$rxml = $this->xmlrpcValue($result);
			$outxml = $this->xmlrpcValueXML($rxml);
			$outxml = "<methodResponse><params><param><value>$outxml</value></param></params></methodResponse>";
			$outxml = siconv($outxml,'UTF-8',$this->charset);
			$this->xmlrpcOutXML($outxml);
		}
	}

	function xmlrpcApi() {
		$api = array (

			// MetaWeblog API
			'metaWeblog.newPost' => 'newPost',
			'metaWeblog.editPost' => 'editPost',
			'metaWeblog.getPost' => 'getPost',
			'metaWeblog.newMediaObject' => 'newMediaObject',
			'metaWeblog.getCategories' => 'getCategories',
			'metaWeblog.getRecentPosts' => 'getRecentPosts',
			
			//WordPress API
			'mt.getCategoryList' =>  'getCategoryList',
			'mt.setPostCategories' =>  'setPostCategories',

			// Blogger API
			'blogger.getUsersBlogs' => 'getUserBlog',
			'blogger.getUserInfo' => 'getUserInfo',
			'blogger.deletePost' => 'deletePost',
			'blogger.getPost' => 'getPost',
			'blogger.getRecentPosts' => 'getRecentPosts',
			'blogger.newPost' => 'newPost',
			'blogger.editPost' => 'editPost'
		);
		return $api;
	}

	function xmlrpcParse($data) {
		$this->xmlmessage->messages = preg_replace('/<\?xml(.*)?\?'.'>/', '', $data);
		if (trim($this->xmlmessage->messages) == '') {
			return false;
		}

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($parser, $this->xmlmessage);
		xml_set_element_handler($parser, array (& $this, 'xmltag_open'), array (& $this, 'xmltag_close'));
		xml_set_character_data_handler($parser, array (& $this,	'xml_data'));
		$message = $this->xmlmessage->messages;
		if (!xml_parse($parser, $message)) {
			return false;
		}
		xml_parser_free($parser);
		if ($this->xmlmessage->messageType == 'fault') {
			return false;
		}
		return true;
	}

	function xmltag_open($parser, $tag, $attr) {
		$this->xmlmessage->tag_content = '';
		$this->xmlmessage->last_open = $tag;
		switch ($tag) {
			case 'methodCall' :
			case 'methodResponse' :
			case 'fault' :
				$this->xmlmessage->messageType = $tag;
				break;
			case 'data' :
				$this->xmlmessage->structTypes[] = 'array';
				$this->xmlmessage->structs[] = array();
				break;
			case 'struct' :
				$this->xmlmessage->structTypes[] = 'struct';
				$this->xmlmessage->structs[] = array();
				break;
		}
	}

	function xml_data($parser, $data) {
		$this->xmlmessage->tag_content .= $data;
	}

	function xmltag_close($parser, $tag) {
		$flag = false;
		switch ($tag) {
			case 'int' :
			case 'i4' :
				$value = intval(trim($this->xmlmessage->tag_content));
				$flag = true;
				break;
			case 'double' :
				$value = (double) trim($this->xmlmessage->tag_content);
				$flag = true;
				break;
			case 'string' :
				$value = $this->xmlmessage->tag_content;
				$flag = true;
				break;
			case 'dateTime.iso8601' :
				$value = $this->convertDate($this->xmlmessage->tag_content);
				$flag = true;
				break;
			case 'value' :
				if (trim($this->xmlmessage->tag_content) != '' || $this->xmlmessage->last_open == 'value') {
					$value = (string) trim($this->xmlmessage->tag_content);
					$flag = true;
				}
				break;
			case 'boolean' :
				$value = (boolean) trim($this->xmlmessage->tag_content);
				$flag = true;
				break;
			case 'base64' :
				$value = saddslashes(base64_decode(trim($this->xmlmessage->tag_content)));
				$flag = true;
				break;
			case 'data' :
			case 'struct' :
				$value = array_pop($this->xmlmessage->structs);
				array_pop($this->xmlmessage->structTypes);
				$flag = true;
				break;
			case 'member' :
				array_pop($this->xmlmessage->struct_name);
				break;
			case 'name' :
				$this->xmlmessage->struct_name[] = trim($this->xmlmessage->tag_content);
				break;
			case 'methodName' :
				$this->xmlmessage->methodname = trim($this->xmlmessage->tag_content);
				break;
		}
		if ($flag) {
			if (count($this->xmlmessage->structs) > 0) {
				if ($this->xmlmessage->structTypes[count($this->xmlmessage->structTypes) - 1] == 'struct') {
					$this->xmlmessage->structs[count($this->xmlmessage->structs) - 1][$this->xmlmessage->struct_name[count($this->xmlmessage->struct_name) - 1]] = $value;
				} else {
					$this->xmlmessage->structs[count($this->xmlmessage->structs) - 1][] = $value;
				}
			} else {
				$this->xmlmessage->params[] = $value;
			}
		}

		if (!in_array($tag, array ('data', 'struct', 'member'))) {
			$this->xmlmessage->tag_content = '';
		}
	}

	function xmlrpcValue($data, $type = false) {
		$value = new stdClass();
		$value->data = $data;
		if (!$type) {
			$type = $this->xmlrpcType($value);
		}
		$value->type = $type;
		if ($type == 'struct') {
			foreach ($value->data as $key => $v) {
				$value->data[$key] = $this->xmlrpcValue($v);
			}
		}
		if ($type == 'array') {
			for ($i = 0, $j = count($value->data);$i < $j;$i++) {
				$value->data[$i] = $this->xmlrpcValue($value->data[$i]);
			}
		}
		return $value;
	}

	function xmlrpcValueXML($data) {
		switch ($data->type) {
			case 'boolean' :
				return '<boolean>'.($data->data) ? '1' : '0'.'</boolean>';
				break;
			case 'int' :
				return '<int>'.$data->data.'</int>';
				break;
			case 'double' :
				return '<double>'.$data->data.'</double>';
				break;
			case 'string' :
				return '<string>'.htmlspecialchars($data->data).'</string>';
				break;
			case 'array' :
				$return = '<array><data>';
				foreach ($data->data as $item) {
					$return .= '<value>'.$this->xmlrpcValueXML($item).'</value>';
				}
				$return .= '</data></array>';
				return $return;
				break;
			case 'struct' :
				$return = '<struct>'."\n";
				foreach ($data->data as $name => $item) {
					$return .= '<member><name>'.$name.'</name>';
					$return .= '<value>'.$this->xmlrpcValueXML($item).'</value></member>';
				}
				$return .= '</struct>';
				return $return;
				break;
			case 'date' :
				return '<dateTime.iso8601>'.($data->data->date).'</dateTime.iso8601>';
				break;
			case 'base64' :
				return '<base64>'.base64_encode($data->data).'</base64>';
				break;
			default :
				break;
		}
	}

	function xmlrpcType(& $value) {
		if (is_bool($value->data)) {
			return 'boolean';
		}
		if (is_double($value->data)) {
			return 'double';
		}
		if (is_int($value->data)) {
			return 'int';
		}
		if (is_array($value->data)) {
			return empty ($value->data) || range(0, count($value->data) - 1) === array_keys($value->data) ? 'array' : 'struct';
		}
		if (is_object($value->data)) {
			if ($value->data->is_date) {
				return 'date';
			}
			if ($value->data->is_base64) {
				return 'base64';
			}
			$value->data = get_object_vars($value->data);
			return 'struct';
		}
		return 'string';
	}

	function xmlrpcCall($methodname, $args) {
		$func = $this->callback[$methodname];
		return call_user_func_array(array(&$this, $func), $args);
	}

	function xmlrpcOutXML($xml) {
		$xml = '<?xml version="1.0" encoding="utf-8"?>'."\n".$xml;
		header('Connection: close');
		header('Content-Length: '.strlen($xml));
		header('Content-Type: text/xml');
		header('Date: '.sgmdate('r'));
		echo $xml;
		exit();
	}

	function sendFault($code = 0, $string = 'Error') {
		@header('Content-Type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<methodResponse><fault><value><struct><member><name>faultCode</name><value><i4>';
		echo $code;
		echo '</i4></value></member><member><name>faultString</name><value><string>';
		echo $string;
		echo '</string></value></member></struct></value></fault></methodResponse>';
		exit();
	}

	function convertDate($time) {
		return sstrtotime($time);
	}

	function authUser($username, $password) {
		global $_SGLOBAL;

		$username = addslashes(siconv($username, $this->charset, 'UTF-8'));
		$password = addslashes(siconv($password, $this->charset, 'UTF-8'));

		if($this->member = getpassport($username, $password)) {
			$_SGLOBAL['supe_uid'] = $this->member['uid'];
			$this->member['username'] = addslashes($this->member['username']);
			$_SGLOBAL['supe_username'] = $this->member['username'];
			$_SGLOBAL['timestamp'] = $this->timestamp;
			return true;
		} else {
			$this->sendFault(1, 'Authoried Error.Please check your password.');
		}
	}

	function getClassId($uid, $classname) {
		$class = array();
		if($classname) {
			$classname = addslashes(siconv($classname, $this->charset, 'UTF-8'));
			$query = $this->db->query("SELECT classid FROM ".tname('class')." WHERE uid='$uid' AND classname='$classname'");
			$class = $this->db->fetch_array($query);
		}
		return empty($class['classid'])?0:$class['classid'];
	}

	function getUserBlog($uid, $username, $password) {
		
		$this->authUser($username, $password);
		
		$struct = array();
		$struct[] = array (
			'url' => $this->siteUrl.'space.php?uid='.$this->member['uid'].'&do=blog',
			'blogid' => $this->member['uid'],
			'blogName' => $this->member['username'].'\'s space'
		);

		return $struct;
	}

	function getUserInfo($uid, $username, $password) {
		
		$this->authUser($username, $password);
		
		$struct = array(
			'nickname'  => $this->member['username'],
			'userid'    => $this->member['uid'],
			'url'       => $this->siteUrl.'space.php?uid='.$this->member['uid'],
			'email'     => '',
			'lastname'  => $this->member['username'],
			'firstname' => ''
		);

		return $struct;
	}

	function newMediaObject($uid, $username, $password, $mediaobject = array()) {
		global $_SGLOBAL, $space;
		
		$fileext = fileext($mediaobject['name']);
		if(!in_array($fileext, array('jpg', 'gif', 'png'))) {
			$this->sendFault(500, 'You should choose image file to upload.');
		}
		
		$this->authUser($username, $password);
		
		include_once(S_ROOT.'./source/function_cp.php');
		$struct = array();
		if($stream_save = stream_save(sstripslashes($mediaobject['bits']),'0', $fileext)) {
			$struct['url'] = pic_get($stream_save['filepath'], $stream_save['thumb'], $stream_save['remote'], 0);
		} else {
			$this->sendFault(500, 'Sorry, your image could not be uploaded. Something wrong happened.');
		}
		
		if(!preg_match("/^(http\:\/\/|\/)/i", $struct['url'])) $struct['url'] = $this->siteUrl.$struct['url'];
		return $struct;

	}
	
	function newPost($uid, $username, $password, $post, $publish = true) {
		return $this->opPost($username, $password, $post, 0);
	}

	function editPost($postid, $username, $password, $post, $publish = true) {
		return $this->opPost($username, $password, $post, $postid);
	}
	
	function opPost($username, $password, $post, $postid=0) {
		global $_SGLOBAL;

		$this->authUser($username, $password);
		$postid = intval($postid);
		include_once(S_ROOT.'./source/function_blog.php');
		$uid = $this->member['uid'];
		$old_post = array();
		if($postid) {
			$query = $this->db->query("SELECT bf.*, b.* FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid WHERE b.blogid='$postid' AND b.uid='$uid'");
			if(!$old_post = $this->db->fetch_array($query)) {
				$this->sendFault(500, 'Sorry, your entry could not be posted. Something wrong happened.');
			}
		}
		
		$post['title'] = siconv($post['title'],$this->charset,'UTF-8');
		
		$post['description'] = isset($post['content'])?$post['content']:$post['description'];
		$post['description'] = siconv($post['description'],$this->charset,'UTF-8');
		
		$blog_post_data = array(
			'classid' => intval($this->getClassId($uid, $post['categories'][0])),
			'subject' => addslashes($post['title']),
			'message' => addslashes($post['description']),
			'tag' => addslashes(empty($post['tagwords'])?'':siconv(implode(' ', $post['tagwords']), $this->charset, 'UTF-8'))
		);

		if($result = blog_post($blog_post_data, $old_post)) {
			return $result['blogid'];
		} else {
			$this->sendFault(500, 'Sorry, your entry could not be posted. Something wrong happened.');
		}
	}
	
	function deletePost($blogname, $blogid, $username, $password, $boolean = true){
		global $_SGLOBAL;
		
		$this->authUser($username, $password);

		include_once(S_ROOT.'./source/function_delete.php');
		$blogid = intval($blogid);
		if(deleteblogs(array($blogid))) {
			return true;
		} else {
			return false;
		}
	}

	function getCategories($blogid, $username, $password) {
		
		$this->authUser($username, $password);
		
		$struct = array();
		$struct[] = array (
			'description' => '',
			'htmlUrl' => $this->siteUrl.'space.php?uid='.$blogid.'&ac=blogs',
			'rssUrl' => '',
			'title' => '',
			'categoryid' => 0
		);

		$uid = $this->member['uid'];
		$query = $this->db->query("SELECT classid, classname FROM ".tname('class')." WHERE uid='$uid'");
		while ($cats = $this->db->fetch_array($query)) {
			$struct[] = array (
				'description' => $cats['classname'],
				'htmlUrl' => $this->siteUrl.'space.php?uid='.$blogid.'&ac=blogs&classid='.$cats['classid'],
				'rssUrl' => '',
				'title' => $cats['classname'],
				'categoryid' => $cats['classid']
			);
		}

		return $struct;
	}

	function getCategoryList($uid, $username, $password) {
		
		$this->authUser($username, $password);
		
		$struct = array();
		$struct[] = array ('categoryId' => 0, 'categoryName' => '');

		$uid = $this->member['uid'];
		$query = $this->db->query("SELECT classid, classname FROM ".tname('class')." WHERE uid='$uid'");
		while ($cats = $this->db->fetch_array($query)) {
			$struct[] = array (
				'categoryId' => $cats['classid'],
				'categoryName' => $cats['classname']
			);
		}

		return $struct;
	}

	function getPost($postid, $username, $password) {
		
		$this->authUser($username, $password);
		
		$struct = array();
		$postid = intval($postid);
		$uid = $this->member['uid'];
		$query = $this->db->query("SELECT bf.message, b.blogid, b.subject, b.dateline, c.classid, c.classname FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid LEFT JOIN ".tname('class')." c ON c.classid=b.classid WHERE b.blogid='$postid' AND b.uid='$uid'");
		if($item = $this->db->fetch_array($query)) {
			$item['dateline'] = sgmdate('Ymd\TH:i:s', $item['dateline']);
			$struct = array(
				'userid' => $uid,
				'dateCreated' => $item['dateline'],
				'title' => $item['subject'],
				'categories' => array($item['classname']),
				'description' => $item['message'],
				'content' => $item['message']
			);
		}

		return $struct;
	}

	function getRecentPosts($type, $username, $password, $num = 50) {

		$this->authUser($username, $password);
		
		$struct = array();

		$uid = $this->member['uid'];
		$num = intval($num);
		if($num < 1) $num = 1;
	
		$query = $this->db->query("SELECT bf.message, b.blogid, b.subject, b.dateline FROM ".tname('blog')." b LEFT JOIN ".tname('blogfield')." bf ON bf.blogid=b.blogid WHERE b.uid ='$uid' ORDER BY b.dateline DESC LIMIT 0,$num");
		while($item = $this->db->fetch_array($query)) {
			$item['dateline'] = sgmdate('Ymd\TH:i:s', $item['dateline']);
			$struct[] = array(
				'postid' => $item['blogid'],
				'userid' => $uid,
				'dateCreated' => $item['dateline'],
				'title' => $item['subject'],
				'categories' => array($item['classname']),
				'description' => $item['message'],
				'content' => $item['message']
			);
		}

		return $struct;
	}
}

?>