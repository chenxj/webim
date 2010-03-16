<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: class_zib.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//常量定义
define( 'PCLZIP_READ_BLOCK_SIZE', 2048 );
define( 'PCLZIP_SEPARATOR', ',' );
define( 'PCLZIP_ERROR_EXTERNAL', 0 );
define( 'PCLZIP_TEMPORARY_DIR', '' );

//错误信息定义
define( 'PCLZIP_ERR_USER_ABORTED', 2 );
define( 'PCLZIP_ERR_NO_ERROR', 0 );
define( 'PCLZIP_ERR_WRITE_OPEN_FAIL', -1 );
define( 'PCLZIP_ERR_READ_OPEN_FAIL', -2 );
define( 'PCLZIP_ERR_INVALID_PARAMETER', -3 );
define( 'PCLZIP_ERR_MISSING_FILE', -4 );
define( 'PCLZIP_ERR_FILENAME_TOO_LONG', -5 );
define( 'PCLZIP_ERR_INVALID_ZIP', -6 );
define( 'PCLZIP_ERR_BAD_EXTRACTED_FILE', -7 );
define( 'PCLZIP_ERR_DIR_CREATE_FAIL', -8 );
define( 'PCLZIP_ERR_BAD_EXTENSION', -9 );
define( 'PCLZIP_ERR_BAD_FORMAT', -10 );
define( 'PCLZIP_ERR_DELETE_FILE_FAIL', -11 );
define( 'PCLZIP_ERR_RENAME_FILE_FAIL', -12 );
define( 'PCLZIP_ERR_BAD_CHECKSUM', -13 );
define( 'PCLZIP_ERR_INVALID_ARCHIVE_ZIP', -14 );
define( 'PCLZIP_ERR_MISSING_OPTION_VALUE', -15 );
define( 'PCLZIP_ERR_INVALID_OPTION_VALUE', -16 );
define( 'PCLZIP_ERR_ALREADY_A_DIRECTORY', -17 );
define( 'PCLZIP_ERR_UNSUPPORTED_COMPRESSION', -18 );
define( 'PCLZIP_ERR_UNSUPPORTED_ENCRYPTION', -19 );

//参数定义
define( 'PCLZIP_OPT_PATH', 77001 );
define( 'PCLZIP_OPT_ADD_PATH', 77002 );
define( 'PCLZIP_OPT_REMOVE_PATH', 77003 );
define( 'PCLZIP_OPT_REMOVE_ALL_PATH', 77004 );
define( 'PCLZIP_OPT_SET_CHMOD', 77005 );
define( 'PCLZIP_OPT_EXTRACT_AS_STRING', 77006 );
define( 'PCLZIP_OPT_NO_COMPRESSION', 77007 );
define( 'PCLZIP_OPT_BY_NAME', 77008 );
define( 'PCLZIP_OPT_BY_INDEX', 77009 );
define( 'PCLZIP_OPT_BY_EREG', 77010 );
define( 'PCLZIP_OPT_BY_PREG', 77011 );
define( 'PCLZIP_OPT_COMMENT', 77012 );
define( 'PCLZIP_OPT_ADD_COMMENT', 77013 );
define( 'PCLZIP_OPT_PREPEND_COMMENT', 77014 );
define( 'PCLZIP_OPT_EXTRACT_IN_OUTPUT', 77015 );
define( 'PCLZIP_OPT_REPLACE_NEWER', 77016 );
define( 'PCLZIP_OPT_STOP_ON_ERROR', 77017 );

define( 'PCLZIP_CB_PRE_EXTRACT', 78001 );
define( 'PCLZIP_CB_POST_EXTRACT', 78002 );
define( 'PCLZIP_CB_PRE_ADD', 78003 );
define( 'PCLZIP_CB_POST_ADD', 78004 );

//Zip 类
class Zip {
	var $zipname = '';
	var $zip_fd = 0;
	var $error_code = 1;
	var $error_string = '';
	var $magic_quotes_status;

	function Zip($p_zipname) {
		if(!function_exists('gzopen')) {
			die('Abort '.basename(__FILE__).': Missing zlib extensions');
		}
		
		$this->zipname = $p_zipname;
		$this->zip_fd = 0;
		$this->magic_quotes_status = -1;

		return;
	}

	function create($p_filelist) {
		$v_result=1;
		$this->privErrorReset();

		$v_options = array();
		$v_add_path = "";
		$v_remove_path = "";
		$v_remove_all_path = false;
		$v_options[PCLZIP_OPT_NO_COMPRESSION] = FALSE;

		$v_size = func_num_args();

		if($v_size > 1) {
			$v_arg_list = &func_get_args();
			array_shift($v_arg_list);
			$v_size--;

			if ((is_integer($v_arg_list[0])) && ($v_arg_list[0] > 77000)) {
				$v_result = $this->privParseOptions($v_arg_list, $v_size, $v_options,
													array (	PCLZIP_OPT_REMOVE_PATH => 'optional',
															PCLZIP_OPT_REMOVE_ALL_PATH => 'optional',
															PCLZIP_OPT_NO_COMPRESSION => 'optional'
														));

				if($v_result != 1) {
					return 0;
				}
				if(isset($v_options[PCLZIP_OPT_REMOVE_PATH])) {
					$v_remove_path = $v_options[PCLZIP_OPT_REMOVE_PATH];
				}
				if(isset($v_options[PCLZIP_OPT_REMOVE_ALL_PATH])) {
					$v_remove_all_path = $v_options[PCLZIP_OPT_REMOVE_ALL_PATH];
				}

			} else {
				$v_add_path = $v_arg_list[0];

				if($v_size == 2) {
					 $v_remove_path = $v_arg_list[1];
				} else if($v_size > 2) {
					Zip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid number / type of arguments");
					return 0;
				}
			}
		}
		$p_result_list = array();

		if(is_array($p_filelist)) {
			$v_result = $this->privCreate($p_filelist, $p_result_list, $v_add_path, $v_remove_path, $v_remove_all_path, $v_options);
		} elseif (is_string($p_filelist)) {
			 $v_list = explode(PCLZIP_SEPARATOR, $p_filelist);
			 $v_result = $this->privCreate($v_list, $p_result_list, $v_add_path, $v_remove_path, $v_remove_all_path, $v_options);
		} else {
			Zip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid variable type p_filelist");
			$v_result = PCLZIP_ERR_INVALID_PARAMETER;
		}
		if($v_result != 1) {
			return 0;
		}
		return $p_result_list;
	}

	function privParseOptions(&$p_options_list, $p_size, &$v_result_list, $v_requested_options=false) {
		$v_result=1;
		$i=0;

		while($i < $p_size) {
			if (!isset($v_requested_options[$p_options_list[$i]])) {
				Zip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid optional parameter '".$p_options_list[$i]."' for this method");
				return Zip::errorCode();
			}

			switch ($p_options_list[$i]) {
				case PCLZIP_OPT_PATH :
				case PCLZIP_OPT_REMOVE_PATH :
				case PCLZIP_OPT_ADD_PATH :
					if(($i+1) >= $p_size) {
						Zip::privErrorLog(PCLZIP_ERR_MISSING_OPTION_VALUE, "Missing parameter value for option '".PclZipUtilOptionText($p_options_list[$i])."'");
						return Zip::errorCode();
					}
					$v_result_list[$p_options_list[$i]] = PclZipUtilTranslateWinPath($p_options_list[$i+1], false);
					$i++;
				break;
				
				case PCLZIP_OPT_REMOVE_ALL_PATH :
				case PCLZIP_OPT_EXTRACT_AS_STRING :
				case PCLZIP_OPT_NO_COMPRESSION :
				case PCLZIP_OPT_EXTRACT_IN_OUTPUT :
				case PCLZIP_OPT_REPLACE_NEWER :
				case PCLZIP_OPT_STOP_ON_ERROR :
					$v_result_list[$p_options_list[$i]] = true;
				break;
			}
			$i++;
		}

		if($v_requested_options !== false) {
			for($key=reset($v_requested_options); $key=key($v_requested_options); $key=next($v_requested_options)) {
				if($v_requested_options[$key] == 'mandatory') {
					if(!isset($v_result_list[$key])) {
						Zip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Missing mandatory parameter ".PclZipUtilOptionText($key)."(".$key.")");
						return Zip::errorCode();
					}
				}
			}
		}
		return $v_result;
	}

	function privCreate($p_list, &$p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_options) {
		$v_result=1;
		$v_list_detail = array();

		$this->privDisableMagicQuotes();

		if(($v_result = $this->privOpenFd('wb')) != 1) {
			return $v_result;
		}

		$v_result = $this->privAddList($p_list, $p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_options);

		$this->privCloseFd();
		$this->privSwapBackMagicQuotes();

		return $v_result;
	}

	function privOpenFd($p_mode) {
		$v_result=1;

		if($this->zip_fd != 0) {
			Zip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Zip file \''.$this->zipname.'\' already open');
			return Zip::errorCode();
		}

		if(($this->zip_fd = @fopen($this->zipname, $p_mode)) == 0) {
			Zip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, 'Unable to open archive \''.$this->zipname.'\' in '.$p_mode.' mode');
			return Zip::errorCode();
		}

		return $v_result;
	}

	function privCloseFd() {
		$v_result=1;

		if($this->zip_fd != 0) {
			@fclose($this->zip_fd);
		}
		$this->zip_fd = 0;

		return $v_result;
	}

	function privAddList($p_list, &$p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_options) {
		$v_result=1;
		$v_header_list = array();

		if(($v_result = $this->privAddFileList($p_list, $v_header_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_options)) != 1) {
			return $v_result;
		}

		$v_offset = @ftell($this->zip_fd);

		for($i=0, $v_count=0; $i<sizeof($v_header_list); $i++) {
			if($v_header_list[$i]['status'] == 'ok') {
				if(($v_result = $this->privWriteCentralFileHeader($v_header_list[$i])) != 1) {
					return $v_result;
				}
				$v_count++;
			}
			$this->privConvertHeader2FileInfo($v_header_list[$i], $p_result_list[$i]);
		}

		$v_size = @ftell($this->zip_fd)-$v_offset;

		if(($v_result = $this->privWriteCentralHeader($v_count, $v_size, $v_offset/*, $v_comment*/)) != 1) {
			unset($v_header_list);
			return $v_result;
		}
		return $v_result;
	}

	function privAddFileList($p_list, &$p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_options) {
		$v_result=1;
		$v_header = array();

		$v_nb = sizeof($p_result_list);

		for($j=0; ($j<count($p_list)) && ($v_result==1); $j++) {
			$p_filename = PclZipUtilTranslateWinPath($p_list[$j], false);

			if($p_filename == "") {
				continue;
			}
			if(!file_exists($p_filename)) {
				Zip::privErrorLog(PCLZIP_ERR_MISSING_FILE, "File '$p_filename' does not exists");
				return Zip::errorCode();
			}
			if((is_file($p_filename)) || ((is_dir($p_filename)) && !$p_remove_all_dir)) {
				if(($v_result = $this->privAddFile($p_filename, $v_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_options)) != 1) {
					return $v_result;
				}
				$p_result_list[$v_nb++] = $v_header;
			}
			if(@is_dir($p_filename)) {
				if($p_filename != ".") {
					$v_path = $p_filename."/";
				} else {
					$v_path = "";

				}
				if($p_hdir = @opendir($p_filename)) {
					while(($p_hitem = @readdir($p_hdir)) !== false) {
						if(($p_hitem == '.') || ($p_hitem == '..')) {
							continue;
						}
						if(@is_file($v_path.$p_hitem)) {
							if(($v_result = $this->privAddFile($v_path.$p_hitem, $v_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_options)) != 1) {
								return $v_result;
							}
							$p_result_list[$v_nb++] = $v_header;
						} elseif(@is_dir($v_path.$p_hitem)) {
							$p_temp_list[0] = $v_path.$p_hitem;
							$v_result = $this->privAddFileList($p_temp_list, $p_result_list, $p_add_dir, $p_remove_dir, $p_remove_all_dir, $p_options);
							$v_nb = sizeof($p_result_list);
						}
					}
					@closedir($p_hdir);
				}
				unset($p_temp_list);
				unset($p_hdir);
				unset($p_hitem);
			}
		}
		return $v_result;
	}

	function privAddFile($p_filename, &$p_header, $p_add_dir, $p_remove_dir, $p_remove_all_dir, &$p_options) {
		$v_result=1;

		if($p_filename == "") {
			Zip::privErrorLog(PCLZIP_ERR_INVALID_PARAMETER, "Invalid file list parameter (invalid or empty list)");
			return Zip::errorCode();
		}

		$v_stored_filename = $p_filename;

		if($p_remove_all_dir) {
			$v_stored_filename = basename($p_filename);
		} elseif($p_remove_dir != "") {
			if(substr($p_remove_dir, -1) != '/') {
				$p_remove_dir .= "/";
			}
			if((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./")) {
				if((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./")) {
					$p_remove_dir = "./".$p_remove_dir;
				}
				if((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./")) {
					$p_remove_dir = substr($p_remove_dir, 2);
				}
			}

			$v_compare = PclZipUtilPathInclusion($p_remove_dir, $p_filename);

			if($v_compare > 0) {
				if($v_compare == 2) {
					$v_stored_filename = "";
				} else {
					$v_stored_filename = substr($p_filename, strlen($p_remove_dir));
				}
			}
		}
		if($p_add_dir != "") {
			if(substr($p_add_dir, -1) == "/") {
				$v_stored_filename = $p_add_dir.$v_stored_filename;
			} else {
				$v_stored_filename = $p_add_dir."/".$v_stored_filename;
			}
		}
		$v_stored_filename = PclZipUtilPathReduction($v_stored_filename);
			
		clearstatcache();
		$p_header['version'] = 20;
		$p_header['version_extracted'] = 10;
		$p_header['flag'] = 0;
		$p_header['compression'] = 0;
		$p_header['mtime'] = filemtime($p_filename);
		$p_header['crc'] = 0;
		$p_header['compressed_size'] = 0;
		$p_header['size'] = filesize($p_filename);
		$p_header['filename_len'] = strlen($p_filename);
		$p_header['extra_len'] = 0;
		$p_header['comment_len'] = 0;
		$p_header['disk'] = 0;
		$p_header['internal'] = 0;
		$p_header['external'] = (is_file($p_filename)?0x00000000:0x00000010);
		$p_header['offset'] = 0;
		$p_header['filename'] = $p_filename;
		$p_header['stored_filename'] = $v_stored_filename;
		$p_header['extra'] = '';
		$p_header['comment'] = '';
		$p_header['status'] = 'ok';
		$p_header['index'] = -1;
		
		if($p_header['stored_filename'] == "") {
			$p_header['status'] = "filtered";
		}
		if(strlen($p_header['stored_filename']) > 0xFF) {
			$p_header['status'] = 'filename_too_long';
		}
		if($p_header['status'] == 'ok') {
			if(is_file($p_filename)) {
				if(($v_file = @fopen($p_filename, "rb")) == 0) {
					Zip::privErrorLog(PCLZIP_ERR_READ_OPEN_FAIL, "Unable to open file '$p_filename' in binary read mode");
					return Zip::errorCode();
				}
				if($p_options[PCLZIP_OPT_NO_COMPRESSION]) {
					$v_content_compressed = @fread($v_file, $p_header['size']);
					$p_header['crc'] = @crc32($v_content_compressed);
					$p_header['compressed_size'] = $p_header['size'];
					$p_header['compression'] = 0;
				} else {
					$v_content = @fread($v_file, $p_header['size']);
					$p_header['crc'] = @crc32($v_content);
					$v_content_compressed = @gzdeflate($v_content);
					$p_header['compressed_size'] = strlen($v_content_compressed);
					$p_header['compression'] = 8;
				}
				if(($v_result = $this->privWriteFileHeader($p_header)) != 1) {
					@fclose($v_file);
					return $v_result;
				}

				@fwrite($this->zip_fd, $v_content_compressed, $p_header['compressed_size']);
				@fclose($v_file);
			} else {
				if(@substr($p_header['stored_filename'], -1) != '/') {
					$p_header['stored_filename'] .= '/';
				}
				$p_header['size'] = 0;
				$p_header['external'] = 0x00000010;

				if(($v_result = $this->privWriteFileHeader($p_header)) != 1) {
					return $v_result;
				}
			}
		}
	
		return $v_result;
	}

	function privWriteFileHeader(&$p_header) {
		$v_result=1;
		 $p_header['offset'] = ftell($this->zip_fd);
		 $v_date = getdate($p_header['mtime']);
		 $v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
		 $v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];
		 $v_binary_data = pack("VvvvvvVVVvv", 0x04034b50,
							$p_header['version_extracted'], $p_header['flag'],
							$p_header['compression'], $v_mtime, $v_mdate,
							$p_header['crc'], $p_header['compressed_size'],
							$p_header['size'],
							strlen($p_header['stored_filename']),
							$p_header['extra_len']);
		 
		 fputs($this->zip_fd, $v_binary_data, 30);

		 if(strlen($p_header['stored_filename']) != 0) {
			 fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
		 }
		 if($p_header['extra_len'] != 0) {
			 fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
		 }
		 return $v_result;
	}

	function privWriteCentralFileHeader(&$p_header) {
		$v_result=1;
		$v_date = getdate($p_header['mtime']);
		$v_mtime = ($v_date['hours']<<11) + ($v_date['minutes']<<5) + $v_date['seconds']/2;
		$v_mdate = (($v_date['year']-1980)<<9) + ($v_date['mon']<<5) + $v_date['mday'];
		$v_binary_data = pack("VvvvvvvVVVvvvvvVV", 0x02014b50,
							$p_header['version'], $p_header['version_extracted'],
							$p_header['flag'], $p_header['compression'],
							$v_mtime, $v_mdate, $p_header['crc'],
							$p_header['compressed_size'], $p_header['size'],
							strlen($p_header['stored_filename']),
							$p_header['extra_len'], $p_header['comment_len'],
							$p_header['disk'], $p_header['internal'],
							$p_header['external'], $p_header['offset']);

		fputs($this->zip_fd, $v_binary_data, 46);

		if(strlen($p_header['stored_filename']) != 0) {
			fputs($this->zip_fd, $p_header['stored_filename'], strlen($p_header['stored_filename']));
		}
		if($p_header['extra_len'] != 0) {
			fputs($this->zip_fd, $p_header['extra'], $p_header['extra_len']);
		}
		if($p_header['comment_len'] != 0) {
			fputs($this->zip_fd, $p_header['comment'], $p_header['comment_len']);
		}
		return $v_result;
	}

	function privWriteCentralHeader($p_nb_entries, $p_size, $p_offset/*, $p_comment*/) {
		$v_result=1;
		$v_binary_data = pack("VvvvvVVv", 0x06054b50, 0, 0, $p_nb_entries,
							$p_nb_entries, $p_size,
							$p_offset, 0);

		fputs($this->zip_fd, $v_binary_data, 22);
		/*
		if(strlen($p_comment) != 0) {
			fputs($this->zip_fd, $p_comment, strlen($p_comment));
		}
		*/
		return $v_result;
	}

	function privConvertHeader2FileInfo($p_header, &$p_info) {
		$v_result=1;
		$p_info['filename'] = $p_header['filename'];
		$p_info['stored_filename'] = $p_header['stored_filename'];
		$p_info['size'] = $p_header['size'];
		$p_info['compressed_size'] = $p_header['compressed_size'];
		$p_info['mtime'] = $p_header['mtime'];
		$p_info['comment'] = $p_header['comment'];
		$p_info['folder'] = (($p_header['external']&0x00000010)==0x00000010);
		$p_info['index'] = $p_header['index'];
		$p_info['status'] = $p_header['status'];

		return $v_result;
	}

	function privErrorLog($p_error_code=0, $p_error_string='') {

		if(PCLZIP_ERROR_EXTERNAL == 1) {
			PclError($p_error_code, $p_error_string);
		} else {
			$this->error_code = $p_error_code;
			$this->error_string = $p_error_string;
		}
		echo $this->error_string;
	}

	function privErrorReset() {
		
		if(PCLZIP_ERROR_EXTERNAL == 1) {
			PclErrorReset();
		} else {
			$this->error_code = 0;
			$this->error_string = '';
		}
	}

	function privDisableMagicQuotes() {
		$v_result=1;

		if((!function_exists("get_magic_quotes_runtime")) || (!function_exists("set_magic_quotes_runtime"))) {
			return $v_result;
		}
		if($this->magic_quotes_status != -1) {
			return $v_result;
		}

		$this->magic_quotes_status = @get_magic_quotes_runtime();

		if($this->magic_quotes_status == 1) {
			@set_magic_quotes_runtime(0);
		}
		return $v_result;
	}

	function privSwapBackMagicQuotes() {
		$v_result=1;
		if((!function_exists("get_magic_quotes_runtime")) || (!function_exists("set_magic_quotes_runtime"))) {
			return $v_result;
		}
		if($this->magic_quotes_status != -1) {
			return $v_result;
		}
		if($this->magic_quotes_status == 1) {
			@set_magic_quotes_runtime($this->magic_quotes_status);
		}
		return $v_result;
	}

	function errorCode() {
		if (PCLZIP_ERROR_EXTERNAL == 1) {
			return(PclErrorCode());
		} else {
			return($this->error_code);
		}
	}
}

function PclZipUtilTranslateWinPath($p_path, $p_remove_disk_letter=true) {
	if(stristr(php_uname(), 'windows')) {
		if(($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) != false)) {
			$p_path = substr($p_path, $v_position+1);
		}
		if((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
			$p_path = strtr($p_path, '\\', '/');
		}
	}
	return $p_path;
}

function PclZipUtilPathInclusion($p_dir, $p_path) {
	$v_result = 1;
	$v_list_dir = explode("/", $p_dir);
	$v_list_dir_size = sizeof($v_list_dir);
	$v_list_path = explode("/", $p_path);
	$v_list_path_size = sizeof($v_list_path);

	$i = 0;
	$j = 0;
	while(($i < $v_list_dir_size) && ($j < $v_list_path_size) && ($v_result)) {
		if($v_list_dir[$i] == '') {
			$i++;
			continue;
		}
		if($v_list_path[$j] == '') {
			$j++;
			continue;
		}
		if(($v_list_dir[$i] != $v_list_path[$j]) && ($v_list_dir[$i] != '') && ( $v_list_path[$j] != '')) {
			$v_result = 0;
		}

		$i++;
		$j++;
	}

	if($v_result) {
		while (($j < $v_list_path_size) && ($v_list_path[$j] == '')) $j++;
		while (($i < $v_list_dir_size) && ($v_list_dir[$i] == '')) $i++;

		if(($i >= $v_list_dir_size) && ($j >= $v_list_path_size)) {
			$v_result = 2;
		} elseif($i < $v_list_dir_size) {
			$v_result = 0;
		}
	}
	return $v_result;
}

function PclZipUtilOptionText($p_option) {
	$v_list = get_defined_constants();
	for(reset($v_list); $v_key = key($v_list); next($v_list)) {
		$v_prefix = substr($v_key, 0, 10);
		if((($v_prefix == 'PCLZIP_OPT') || ($v_prefix == 'PCLZIP_CB_')) && ($v_list[$v_key] == $p_option) ) {
			return $v_key;
		}
	}
	$v_result = 'Unknown';
	return $v_result;
}

function PclZipUtilPathReduction($p_dir) {
	$v_result = "";
	if($p_dir != "") {
		$v_list = explode("/", $p_dir);
		$v_skip = 0;

		for($i=sizeof($v_list)-1; $i>=0; $i--) {
			if($v_list[$i] == ".") {
			
			} elseif($v_list[$i] == "..") {
				$v_skip++;
			} elseif($v_list[$i] == "") {
				if($i == 0) {
					$v_result = "/".$v_result;
					if($v_skip > 0) {
						$v_result = $p_dir;
						$v_skip = 0;
					}
				} elseif($i == (sizeof($v_list)-1)) {
					$v_result = $v_list[$i];
				} else {

				}
			} else {
				if($v_skip > 0) {
					$v_skip--;
				} else {
					$v_result = $v_list[$i].($i!=(sizeof($v_list)-1)?"/".$v_result:"");
				}
			}
		}
		if($v_skip > 0) {
			while($v_skip > 0) {
				$v_result = '../'.$v_result;
				$v_skip--;
			}
		}
	}
	return $v_result;
}

class SimpleUnzip {
// 2003-12-02 - HB >
        var $Comment = '';
// 2003-12-02 - HB <

        var $Entries = array();

        var $Name = '';

        var $Size = 0;

        var $Time = 0;

        function SimpleUnzip($in_FileName = '') {
            if($in_FileName !== '') {
                SimpleUnzip::ReadFile($in_FileName);
            }
        } // end of the 'SimpleUnzip' constructor

        function Count() {
            return count($this->Entries);
        } // end of the 'Count()' method

        function GetData($in_Index) {
            return $this->Entries[$in_Index]->Data;
        } // end of the 'GetData()' method

        function GetEntry($in_Index) {
            return $this->Entries[$in_Index];
        } // end of the 'GetEntry()' method

        function GetError($in_Index) {
            return $this->Entries[$in_Index]->Error;
        } // end of the 'GetError()' method

        function GetErrorMsg($in_Index) {
            return $this->Entries[$in_Index]->ErrorMsg;
        } // end of the 'GetErrorMsg()' method

        function GetName($in_Index) {
            return $this->Entries[$in_Index]->Name;
        } // end of the 'GetName()' method

        function GetPath($in_Index) {
            return $this->Entries[$in_Index]->Path;
        } // end of the 'GetPath()' method

        function GetTime($in_Index) {
            return $this->Entries[$in_Index]->Time;
        } // end of the 'GetTime()' method

        function ReadFile($in_FileName) {
            $this->Entries = array();

            // Get file parameters
            $this->Name = $in_FileName;
            $this->Time = filemtime($in_FileName);
            $this->Size = filesize($in_FileName);

            // Read file
            $oF = fopen($in_FileName, 'rb');
            $vZ = fread($oF, $this->Size);
            fclose($oF);

// 2003-12-02 - HB >
            // Cut end of central directory
            $aE = explode("\x50\x4b\x05\x06", $vZ);

            // Easiest way, but not sure if format changes
            //$this->Comment = substr($aE[1], 18);

            // Normal way
            $aP = unpack('x16/v1CL', $aE[1]);
            $this->Comment = substr($aE[1], 18, $aP['CL']);

            // Translates end of line from other operating systems
            $this->Comment = strtr($this->Comment, array("\r\n" => "\n",
                                                         "\r"   => "\n"));
// 2003-12-02 - HB <

            // Cut the entries from the central directory
            $aE = explode("\x50\x4b\x01\x02", $vZ);
            // Explode to each part
            $aE = explode("\x50\x4b\x03\x04", $aE[0]);
            // Shift out spanning signature or empty entry
            array_shift($aE);

            // Loop through the entries
            foreach($aE as $vZ) {
                $aI = array();
                $aI['E']  = 0;
                $aI['EM'] = '';
                // Retrieving local file header information
                $aP = unpack('v1VN/v1GPF/v1CM/v1FT/v1FD/V1CRC/V1CS/V1UCS/v1FNL', $vZ);
                // Check if data is encrypted
                $bE = ($aP['GPF'] && 0x0001) ? TRUE : FALSE;
                $nF = $aP['FNL'];

                // Special case : value block after the compressed data
                if($aP['GPF'] & 0x0008) {
                    $aP1 = unpack('V1CRC/V1CS/V1UCS', substr($vZ, -12));

                    $aP['CRC'] = $aP1['CRC'];
                    $aP['CS']  = $aP1['CS'];
                    $aP['UCS'] = $aP1['UCS'];

                    $vZ = substr($vZ, 0, -12);
                }

                // Getting stored filename
                $aI['N'] = substr($vZ, 26, $nF);

                if(substr($aI['N'], -1) == '/') {
                    // is a directory entry - will be skipped
                    continue;
                }

                // Truncate full filename in path and filename
                $aI['P'] = dirname($aI['N']);
                $aI['P'] = $aI['P'] == '.' ? '' : $aI['P'];
                $aI['N'] = basename($aI['N']);

                $vZ = substr($vZ, 26 + $nF);

                if(strlen($vZ) != $aP['CS']) {
                  $aI['E']  = 1;
                  $aI['EM'] = 'Compressed size is not equal with the value in header information.';
                } else {
                    if($bE) {
                        $aI['E']  = 5;
                        $aI['EM'] = 'File is encrypted, which is not supported from this class.';
                    } else {
                        switch($aP['CM']) {
                            case 0: // Stored
                                // Here is nothing to do, the file ist flat.
                                break;

                            case 8: // Deflated
                                $vZ = gzinflate($vZ);
                                break;

                            case 12: // BZIP2
// 2003-12-02 - HB >
                                if(! extension_loaded('bz2')) {
                                    if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                                      @dl('php_bz2.dll');
                                    } else {
                                      @dl('bz2.so');
                                    }
                                }

                                if(extension_loaded('bz2')) {
// 2003-12-02 - HB <
                                    $vZ = bzdecompress($vZ);
// 2003-12-02 - HB >
                                } else {
                                    $aI['E']  = 7;
                                    $aI['EM'] = "PHP BZIP2 extension not available.";
                                }
// 2003-12-02 - HB <

                                break;

                            default:
                              $aI['E']  = 6;
                              $aI['EM'] = "De-/Compression method {$aP['CM']} is not supported.";
                        }

// 2003-12-02 - HB >
                        if(! $aI['E']) {
// 2003-12-02 - HB <
                            if($vZ === FALSE) {
                                $aI['E']  = 2;
                                $aI['EM'] = 'Decompression of data failed.';
                            } else {
                                if(strlen($vZ) != $aP['UCS']) {
                                    $aI['E']  = 3;
                                    $aI['EM'] = 'Uncompressed size is not equal with the value in header information.';
                                } else {
                                    if(crc32($vZ) != $aP['CRC']) {
                                        $aI['E']  = 4;
                                        $aI['EM'] = 'CRC32 checksum is not equal with the value in header information.';
                                    }
                                }
                            }
// 2003-12-02 - HB >
                        }
// 2003-12-02 - HB <
                    }
                }

                $aI['D'] = $vZ;

                // DOS to UNIX timestamp
                $aI['T'] = mktime(($aP['FT']  & 0xf800) >> 11,
                                  ($aP['FT']  & 0x07e0) >>  5,
                                  ($aP['FT']  & 0x001f) <<  1,
                                  ($aP['FD']  & 0x01e0) >>  5,
                                  ($aP['FD']  & 0x001f),
                                  (($aP['FD'] & 0xfe00) >>  9) + 1980);

                $this->Entries[] = &new SimpleUnzipEntry($aI);
            } // end for each entries

            return $this->Entries;
	} // end of the 'ReadFile()' method
} // end of the 'SimpleUnzip' class

class SimpleUnzipEntry {
        var $Data = '';

        var $Error = 0;

        var $ErrorMsg = '';

        var $Name = '';

        var $Path = '';

        var $Time = 0;

        function SimpleUnzipEntry($in_Entry) {
		$this->Data     = $in_Entry['D'];
		$this->Error    = $in_Entry['E'];
		$this->ErrorMsg = $in_Entry['EM'];
		$this->Name     = $in_Entry['N'];
		$this->Path     = $in_Entry['P'];
		$this->Time     = $in_Entry['T'];
        } // end of the 'SimpleUnzipEntry' constructor
} // end of the 'SimpleUnzipEntry' class

?>