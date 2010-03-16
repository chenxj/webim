<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: function_exif.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

function getimageinfoval($ImageInfo,$val_arr) {
	$InfoVal	=	exif_lang('unknown');
	foreach($val_arr as $name=>$val) {
		if ($name == $ImageInfo) {
			$InfoVal	=	&$val;
			break;
		}
	}
	return $InfoVal;
}

function getexif($img) {

	$imgtype			=	array("", "GIF", "JPG", "PNG", "SWF", "PSD", "BMP", "TIFF(intel byte order)", "TIFF(motorola byte order)", "JPC", "JP2", "JPX", "JB2", "SWC", "IFF", "WBMP", "XBM");
	$Orientation		=	array("", "top left side", "top right side", "bottom right side", "bottom left side", "left side top", "right side top", "right side bottom", "left side bottom");
	$ResolutionUnit		=	exif_lang('resolutionunit');
	$YCbCrPositioning	=	array("", "the center of pixel array", "the datum point");
	$ExposureProgram	=	exif_lang('exposureprogram');
	$MeteringMode_arr	=	exif_lang('meteringmode');
	$Lightsource_arr	=	exif_lang('lightsource');
	$Flash_arr			=	array(
		"0"		=>	"flash did not fire",
		"1"		=>	"flash fired",
		"5"		=>	"flash fired but strobe return light not detected",
		"7"		=>	"flash fired and strobe return light detected",
		);
	
	$exif = @exif_read_data($img,"IFD0");
	if ($exif === false) {
		$new_img_info	=	exif_lang('img_info');
	} else {
		@$exif = exif_read_data($img, 0, true);
		$new_img_info	=	array (
			//"文件信息"		=>	'',
			exif_lang('FileName')			=>	$exif[FILE][FileName],
			exif_lang('FileType')		=>	$imgtype[$exif[FILE][FileType]],
			exif_lang('MimeType')		=>	$exif[FILE][MimeType],
			exif_lang('FileSize')		=>	$exif[FILE][FileSize],
			exif_lang('FileDateTime')			=>	date("Y-m-d H:i:s",$exif[FILE][FileDateTime]),
			//"图像信息"		=>	'',
			exif_lang('ImageDescription')		=>	$exif[IFD0][ImageDescription],
			exif_lang('Make')			=>	$exif[IFD0][Make],
			exif_lang('Model')			=>	$exif[IFD0][Model],
			exif_lang('Orientation')			=>	$Orientation[$exif[IFD0][Orientation]],
			exif_lang('XResolution')		=>	$exif[IFD0][XResolution].$ResolutionUnit[$exif[IFD0][ResolutionUnit]],
			exif_lang('YResolution')		=>	$exif[IFD0][YResolution].$ResolutionUnit[$exif[IFD0][ResolutionUnit]],
			exif_lang('Software')		=>	$exif[IFD0][Software],
			exif_lang('DateTime')		=>	$exif[IFD0][DateTime],
			exif_lang('Artist')			=>	$exif[IFD0][Artist],
			exif_lang('YCbCrPositioning')	=>	$YCbCrPositioning[$exif[IFD0][YCbCrPositioning]],
			exif_lang('Copyright')			=>	$exif[IFD0][Copyright],
			exif_lang('Photographer')		=>	$exif[COMPUTED][Copyright.Photographer],
			exif_lang('Editor')		=>	$exif[COMPUTED][Copyright.Editor],
			//"拍摄信息"		=>	'',
			exif_lang('ExifVersion')		=>	$exif[EXIF][ExifVersion],
			exif_lang('FlashPixVersion')	=>	"Ver. ".number_format($exif[EXIF][FlashPixVersion]/100,2),
			exif_lang('DateTimeOriginal')		=>	$exif[EXIF][DateTimeOriginal],
			exif_lang('DateTimeDigitized')		=>	$exif[EXIF][DateTimeDigitized],
			exif_lang('Height')	=>	$exif[COMPUTED][Height],
			exif_lang('Width')	=>	$exif[COMPUTED][Width],
			/*
			The actual aperture value of lens when the image was taken.
			Unit is APEX.
			To convert this value to ordinary F-number(F-stop),
			calculate this value's power of root 2 (=1.4142).
			For example, if the ApertureValue is '5', F-number is pow(1.41425,5) = F5.6.
			*/
			exif_lang('ApertureValue')			=>	$exif[EXIF][ApertureValue],
			exif_lang('ShutterSpeedValue')		=>	$exif[EXIF][ShutterSpeedValue],
			exif_lang('ApertureFNumber')		=>	$exif[COMPUTED][ApertureFNumber],
			exif_lang('MaxApertureValue')	=>	"F".$exif[EXIF][MaxApertureValue],
			exif_lang('ExposureTime')		=>	$exif[EXIF][ExposureTime],
			exif_lang('FNumber')		=>	$exif[EXIF][FNumber],
			exif_lang('MeteringMode')		=>	getimageinfoval($exif[EXIF][MeteringMode],$MeteringMode_arr),
			exif_lang('LightSource')			=>	getimageinfoval($exif[EXIF][LightSource], $Lightsource_arr),
			exif_lang('Flash')		=>	getimageinfoval($exif[EXIF][Flash], $Flash_arr),
			exif_lang('ExposureMode')		=>	($exif[EXIF][ExposureMode]==1?exif_lang('manual'):exif_lang('auto')),
			exif_lang('WhiteBalance')		=>	($exif[EXIF][WhiteBalance]==1?exif_lang('manual'):exif_lang('auto')),
			exif_lang('ExposureProgram')		=>	$ExposureProgram[$exif[EXIF][ExposureProgram]],
			/*
			Brightness of taken subject, unit is APEX. To calculate Exposure(Ev) from BrigtnessValue(Bv), you must add SensitivityValue(Sv).
			Ev=Bv+Sv   Sv=log((ISOSpeedRating/3.125),2)
			ISO100:Sv=5, ISO200:Sv=6, ISO400:Sv=7, ISO125:Sv=5.32. 
			*/
			exif_lang('ExposureBiasValue')		=>	$exif[EXIF][ExposureBiasValue]."EV",
			exif_lang('ISOSpeedRatings')		=>	$exif[EXIF][ISOSpeedRatings],
			exif_lang('ComponentsConfiguration')		=>	(bin2hex($exif[EXIF][ComponentsConfiguration])=="01020300"?"YCbCr":"RGB"),//'0x04,0x05,0x06,0x00'="RGB" '0x01,0x02,0x03,0x00'="YCbCr"
			exif_lang('CompressedBitsPerPixel')		=>	$exif[EXIF][CompressedBitsPerPixel]."Bits/Pixel",
			exif_lang('FocusDistance')		=>	$exif[COMPUTED][FocusDistance]."m",
			exif_lang('FocalLength')			=>	$exif[EXIF][FocalLength]."mm",
			exif_lang('FocalLengthIn35mmFilm')	=>	$exif[EXIF][FocalLengthIn35mmFilm]."mm",
			/*
			Stores user comment. This tag allows to use two-byte character code or unicode. First 8 bytes describe the character code. 'JIS' is a Japanese character code (known as Kanji).
			'0x41,0x53,0x43,0x49,0x49,0x00,0x00,0x00':ASCII
			'0x4a,0x49,0x53,0x00,0x00,0x00,0x00,0x00':JIS
			'0x55,0x4e,0x49,0x43,0x4f,0x44,0x45,0x00':Unicode
			'0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00':Undefined
			*/
			exif_lang('UserCommentEncoding')	=>	$exif[COMPUTED][UserCommentEncoding],
			exif_lang('UserComment')		=>	$exif[COMPUTED][UserComment],
			exif_lang('ColorSpace')		=>	($exif[EXIF][ColorSpace]==1?"sRGB":"Uncalibrated"),
			exif_lang('ExifImageLength')	=>	$exif[EXIF][ExifImageLength],
			exif_lang('ExifImageWidth')	=>	$exif[EXIF][ExifImageWidth],
			exif_lang('FileSource')		=>	(bin2hex($exif[EXIF][FileSource])==0x03?"digital still camera":"unknown"),
			exif_lang('SceneType')		=>	(bin2hex($exif[EXIF][SceneType])==0x01?"A directly photographed image":"unknown"),
			exif_lang('ThumbFileType')	=>	$exif[COMPUTED][Thumbnail.FileType],
			exif_lang('ThumbMimeType')	=>	$exif[COMPUTED][Thumbnail.MimeType]
		);
	}
	return $new_img_info;
}

function exif_lang($langkey) {
	global $_SGLOBAL;
	
	include_once(S_ROOT.'./language/lang_exif.php');
	return empty($_SGLOBAL['exiflang'][$langkey])?$langkey:$_SGLOBAL['exiflang'][$langkey];
}

?>