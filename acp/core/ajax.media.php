<?php
session_start();
error_reporting(0);

require '../../lib/Medoo.php';
use Medoo\Medoo;

require '../../config.php';


if(is_file('../../config_database.php')) {
	include '../../config_database.php';
	$db_type = 'mysql';
	
	$database = new Medoo([

		'database_type' => 'mysql',
		'database_name' => "$database_name",
		'server' => "$database_host",
		'username' => "$database_user",
		'password' => "$database_psw",
	 
		'charset' => 'utf8',
		'port' => $database_port,
	 
		'prefix' => DB_PREFIX
	]);
	
	$db_content = $database;
	$db_user = $database;
	$db_statistics = $database;	
	
	
	
} else {
	$db_type = 'sqlite';
	
	if(isset($fc_content_files) && is_array($fc_content_files)) {
		/* switch database file $fc_db_content */
		include 'core/contentSwitch.php';
	}
	
	
	define("CONTENT_DB", "../../$fc_db_content");

	$db_content = new Medoo([
		'database_type' => 'sqlite',
		'database_file' => CONTENT_DB
	]);
	
	
}





define("FC_ROOT", str_replace("/acp","",FC_INC_DIR));
define("IMAGES_FOLDER", "../$img_path");
define("FILES_FOLDER", "../$files_path");
define("FC_SOURCE", "backend");



require_once 'access.php';
require_once 'functions.php';
require '../../lib/lang/'.$_SESSION['lang'].'/dict-backend.php';

$set_lang = $_SESSION['lang'];
if(isset($_REQUEST['set_lang'])) {
	$set_lang = $_REQUEST['set_lang'];
}

$form_tpl = file_get_contents('../templates/media-edit-form.tpl');

if(isset($_REQUEST['file'])) {
	$media_filename = strip_tags($_REQUEST['file']);
	if(stripos($_REQUEST['file'],"$files_path") !== FALSE) {
		$preview_src = '<p>Filetype: '.substr(strrchr($media_filename, "."), 1).'</p>';
		$realpath = $media_filename;
		$img_dimensions = '';
		$shortcode = 'file';
	} else {
		$preview_src = '<img src="'. $media_filename.'" class="img-fluid">';
		$realpath = $media_filename;
		list($img_width, $img_height) = getimagesize("../$media_filename");
		$img_dimensions = ' | '.$img_width.' x '.$img_height.' px';
		$shortcode = 'image';
	}	
}

$abs_path = str_replace('../','/',$realpath);
$filesize = filesize("../$realpath");
$rfilesize = readable_filesize(filesize("../$realpath"));
$lastedit = date('d.m.Y H:i',filemtime("../$realpath"));

if(isset($_POST['saveMedia'])) {
	$savedMedia = fc_write_media_data($_POST['realpath'],$_POST['title'],$_POST['notes'],$_POST['keywords'],$_POST['text'],$_POST['url'],$_POST['alt'],$set_lang,$_POST['credit'],$_POST['priority'],$_POST['license'],time(),$filesize,$_POST['version']);
	if($savedMedia == 'success') {
		$message = '<div class="alert alert-success alert-auto-close">'.$lang['db_changed'].'</div>';
	} else {
		$message = '<div class="alert alert-danger alert-auto-close">'.$lang['db_not_changed'].$savedMedia.'</div>';
	}
	$form_tpl = str_replace('{message}', $message, $form_tpl);
} else {
	$form_tpl = str_replace('{message}', '', $form_tpl);
}



$arr_lang = get_all_languages($d='../../lib/lang');
$langSwitch = '<div class="btn-group" role="group">';
foreach($arr_lang as $langs) {
	$btn_status = '';
	if($langs['lang_sign'] == "$set_lang") { $btn_status = 'active'; }
	$langSwitch .= '<a data-fancybox data-type="ajax" class="change-fb btn btn-fc btn-sm '.$btn_status.'" data-src="../acp/core/ajax.media.php?file='.$media_filename.'&folder='.$_REQUEST['folder'].'&set_lang='.$langs['lang_sign'].'" href="javascript:;">'.$langs['lang_sign'].'</a>';
}
$langSwitch .= '</div>';


$media_data = fc_get_media_data($realpath,$set_lang);

$form_tpl = str_replace('{form_action}', "#", $form_tpl);
$form_tpl = str_replace('{filename}', $media_filename, $form_tpl);
$form_tpl = str_replace('{file}', $media_filename, $form_tpl);
$form_tpl = str_replace('{basename}', basename($media_filename), $form_tpl);
$form_tpl = str_replace('{realpath}', $realpath, $form_tpl);
$form_tpl = str_replace('{showpath}', $abs_path, $form_tpl);
$form_tpl = str_replace('{rfilesize}', $rfilesize, $form_tpl);
$form_tpl = str_replace('{image_dimensions}', $img_dimensions, $form_tpl);
$form_tpl = str_replace('{edittime}', $lastedit, $form_tpl);
$form_tpl = str_replace('{folder}', $_REQUEST['folder'], $form_tpl);
$form_tpl = str_replace('{title}', htmlspecialchars($media_data['media_title'], ENT_QUOTES), $form_tpl);
$form_tpl = str_replace('{description}', $media_data['media_description'], $form_tpl);
$form_tpl = str_replace('{keywords}', $media_data['media_keywords'], $form_tpl);
$form_tpl = str_replace('{text}', $media_data['media_text'], $form_tpl);
$form_tpl = str_replace('{label_title}', $lang['label_title'], $form_tpl);
$form_tpl = str_replace('{label_description}', $lang['label_description'], $form_tpl);
$form_tpl = str_replace('{label_keywords}', $lang['label_keywords'], $form_tpl);
$form_tpl = str_replace('{label_alt}', $lang['label_alt'], $form_tpl);
$form_tpl = str_replace('{alt}', $media_data['media_alt'], $form_tpl);
$form_tpl = str_replace('{label_url}', $lang['label_url'], $form_tpl);
$form_tpl = str_replace('{url}', $media_data['media_url'], $form_tpl);
$form_tpl = str_replace('{label_priority}', $lang['label_priority'], $form_tpl);
$form_tpl = str_replace('{priority}', $media_data['media_priority'], $form_tpl);
$form_tpl = str_replace('{label_license}', $lang['label_license'], $form_tpl);
$form_tpl = str_replace('{license}', $media_data['media_license'], $form_tpl);
$form_tpl = str_replace('{label_credits}', $lang['label_credits'], $form_tpl);
$form_tpl = str_replace('{version}', $media_data['media_version'], $form_tpl);
$form_tpl = str_replace('{label_version}', $lang['label_version'], $form_tpl);
$form_tpl = str_replace('{credit}', $media_data['media_credit'], $form_tpl);
$form_tpl = str_replace('{label_notes}', $lang['label_notes'], $form_tpl);
$form_tpl = str_replace('{notes}', $media_data['media_notes'], $form_tpl);
$form_tpl = str_replace('{label_text}', $lang['label_text'], $form_tpl);
$form_tpl = str_replace('{preview}', $preview_src, $form_tpl);
$form_tpl = str_replace('{save}', $lang['save'], $form_tpl);
$form_tpl = str_replace('{set_lang}', $set_lang, $form_tpl);
$form_tpl = str_replace('{filesize}', $filesize, $form_tpl);
$form_tpl = str_replace('{lang_switch}', $langSwitch, $form_tpl);
$form_tpl = str_replace('{shortcode}', $shortcode, $form_tpl);
$form_tpl = str_replace('{token}',$_SESSION['token'],$form_tpl);
echo $form_tpl;


?>

<script>

$(document).ready(function(){
	
	$('a.change-fb').click(function(e) {
		e.preventDefault();
		var target = $(this).data('src');
		$.get(target, function (data) {
			$.fancybox.getInstance().setContent( $.fancybox.getInstance().current, data );
		});	  
	  
	 });
	
	
  $("#media_form").bind("submit", function() {
      $.ajax({
          type : "POST",
          cache : false,
          url: "../acp/core/ajax.media.php",
          data: $(this).serializeArray(),
          success:function(data){
              $.fancybox.getInstance().setContent( $.fancybox.getInstance().current, data );
          }
      });
      return false;
	});
	
	
	$("[data-fancybox]").fancybox({
			type: 'ajax',
			minWidth: '450px',
			height: '90%'
	});
	
	
	setTimeout(function() {
      $(".alert-auto-close").slideUp('slow');
	}, 2000);


});

</script>