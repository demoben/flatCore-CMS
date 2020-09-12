<?php

/**
 * install flatCore
 * create the sqlite database files
 */

if(!defined('INSTALLER')) {
	header("location:../login.php");
	die("PERMISSION DENIED!");
}

$username = $_POST['username'];
$mail = $_POST['mail'];
$psw = $_POST['psw'];

$user_psw_hash = password_hash($psw, PASSWORD_DEFAULT);
$drm_string = "drm_acp_pages|drm_acp_files|drm_acp_user|drm_acp_system|drm_acp_editpages|drm_acp_editownpages|drm_moderator|drm_can_publish";
$user_verified = "verified";
$user_registerdate = time();


/**
 * DATABASE USER
 */

$sql_user_table = fc_generate_sql_query("fc_user.php");
$sql_groups_table = fc_generate_sql_query("fc_groups.php");
$sql_tokens_table = fc_generate_sql_query("fc_tokens.php");

$dbh = new PDO("sqlite:../$fc_db_user");

$dbh->query($sql_user_table);
$dbh->query($sql_groups_table);
$dbh->query($sql_tokens_table);

$sql_insert_admin = "INSERT INTO fc_user (
		user_id, user_class, user_nick, user_verified, user_registerdate, user_drm, user_mail, user_psw_hash
	) VALUES (
		NULL, 'administrator', :username, 'verified', :user_registerdate, :drm_string, :mail, :user_psw_hash
	)";

$sth = $dbh->prepare($sql_insert_admin);
$sth->bindParam(':username', $username, PDO::PARAM_STR);
$sth->bindParam(':user_registerdate', $user_registerdate, PDO::PARAM_STR);
$sth->bindParam(':drm_string', $drm_string, PDO::PARAM_STR);
$sth->bindParam(':mail', $mail, PDO::PARAM_STR);
$sth->bindParam(':user_psw_hash', $user_psw_hash, PDO::PARAM_STR);
$sth->execute();

$dbh = null;



/**
 * DATABASE CONTENT
 */

$sql_feeds_table = fc_generate_sql_query("fc_feeds.php");
$portal_content = file_get_contents("contents/text_welcome.txt");
$example_content = file_get_contents("contents/text_example.txt");
$footer_content = file_get_contents("contents/text_footer.txt");
$agreement_content = file_get_contents("contents/text_agreement.txt");
$email_confirm_content = file_get_contents("contents/text_email_confirm.txt");
$page_lastedit = time();


$sql_portal_site = "INSERT INTO fc_pages (
							page_id , page_language , page_linkname ,
							page_title , page_status , page_content ,
							page_lastedit ,	page_lastedit_from , page_template ,
							page_template_layout , page_sort , page_meta_author ,
							page_meta_date , page_meta_keywords , page_meta_description ,
							page_meta_robots , page_meta_enhanced , page_head_styles ,
							page_head_enhanced , page_modul, page_authorized_users
						) VALUES (
							NULL, '$languagePack', 'Startseite', 'Home',
							'public', '$portal_content', '$page_lastedit',
							'Installer', 'default', 'layout_portal.tpl',
							'portal', 'Installer', '$page_lastedit',
							'Lorem, ipsum, dolor, sit', 'Testseite',
							'all', '', '',
							'',	'',	'' )
							";



$sql_first_site = "INSERT INTO fc_pages (
						page_id , page_language , page_linkname ,
						page_title , page_status , page_permalink,
						page_content , page_lastedit , page_lastedit_from ,
						page_template ,	page_sort ,	page_meta_author ,
						page_meta_date , page_meta_keywords , page_meta_description ,
						page_meta_robots , page_meta_enhanced ,	page_head_styles ,
						page_head_enhanced , page_modul, page_authorized_users 
						) VALUES (
						NULL, '$languagePack', 'Testseite',
						'flatCore',	'public', 'flatcore/',
						'$example_content', '$page_lastedit', 'Installer',
						'use_standard', '100', 'Installer',
						'$page_lastedit', 'Lorem, ipsum, dolor, sit', 'Testseite',
						'all', '', '',
						'', '', '' ) ";


$sql_insert_prefs = "INSERT INTO fc_preferences (
		prefs_id, prefs_status, prefs_pagetitle,
		prefs_pagesubtitle, prefs_template, prefs_showloginform, prefs_xml_sitemap,
		prefs_imagesuffix, prefs_maximagewidth, prefs_maximageheight, prefs_maxfilesize,
		prefs_logfile, prefs_template_layout, prefs_rss_time_offset, prefs_cms_domain, prefs_cms_ssl_domain, prefs_cms_base
		) VALUES (
		NULL, 'active', 'Diese Homepage',
		'rockt mit SQLite und PHP5', 'default', 'yes', 'off',
		'jpg jpeg gif png', '600', '500', '2800',
		'on', 'layout_default.tpl', '216000', '$prefs_cms_domain', '$prefs_cms_ssl_domain', '$prefs_cms_base' )";

$sql_tl_footer_text = "INSERT INTO fc_textlib ( 
						textlib_id , textlib_name , textlib_content , textlib_lang 
						) VALUES (
						NULL , 'footer_text' , '$footer_content' , 'de' )";

$sql_tl_extra_content_text = "INSERT INTO fc_textlib ( 
								textlib_id , textlib_name , textlib_content , textlib_lang
								) VALUES (
								NULL , 'extra_content_text' , '' , 'de' )";

$sql_tl_agreement_text = "INSERT INTO fc_textlib ( 
							textlib_id , textlib_name , textlib_content , textlib_lang
							) VALUES (
							NULL , 'agreement_text' , '$agreement_content' , 'de' )";

$sql_tl_account_confirm = "INSERT INTO fc_textlib ( 
							textlib_id , textlib_name , textlib_content , textlib_lang 
							) VALUES (
							NULL , 'account_confirm' , '<p>Dein Account wurde erfolgreich freigeschaltet.</p>' , 'de' )";

$sql_tl_account_confirm_mail = "INSERT INTO fc_textlib ( 
								textlib_id , textlib_name , textlib_content , textlib_lang 
								) VALUES ( 
								NULL , 'account_confirm_mail' , '$email_confirm_content' , 'de' )";

$sql_tl_no_access = "INSERT INTO fc_textlib ( 
						textlib_id , textlib_name , textlib_content , textlib_lang 
						) VALUES (
						NULL , 'no_access' , 'Zugriff verweigert...' , 'de' )";


$sql_pages_table = fc_generate_sql_query("fc_pages.php");
$sql_pages_cache_table = fc_generate_sql_query("fc_pages_cache.php");
$sql_preferences_table = fc_generate_sql_query("fc_preferences.php");
$sql_textlib_table = fc_generate_sql_query("fc_textlib.php");
$sql_comments_table = fc_generate_sql_query("fc_comments.php");
$sql_media_table = fc_generate_sql_query("fc_media.php");
$sql_labels_table = fc_generate_sql_query("fc_labels.php");
$sql_addons_table = fc_generate_sql_query("fc_addons.php");

$dbh = new PDO("sqlite:../$fc_db_content");

	$dbh->query($sql_pages_table);
	$dbh->query($sql_pages_cache_table);
	$dbh->query($sql_preferences_table);
	$dbh->query($sql_textlib_table);
	$dbh->query($sql_comments_table);
	$dbh->query($sql_media_table);
	$dbh->query($sql_feeds_table);
	$dbh->query($sql_portal_site);
	$dbh->query($sql_first_site);
	$dbh->query($sql_tl_footer_text);
	$dbh->query($sql_tl_extra_content_text);
	$dbh->query($sql_tl_agreement_text);
	$dbh->query($sql_tl_account_confirm);
	$dbh->query($sql_tl_account_confirm_mail);
	$dbh->query($sql_tl_no_access);
	$dbh->query($sql_labels_table);
	$dbh->query($sql_addons_table);

	$dbh->query($sql_insert_prefs);

$dbh = null;


/**
 * DATABASE INDEX
 */
 
 


$dbh = new PDO("sqlite:../$fc_db_index");

$sql_index_excludes_table = fc_generate_sql_query("fc_index_excludes.php");
$sql_index_items_table = fc_generate_sql_query("fc_index_items.php");

$dbh->query($sql_index_excludes_table);
$dbh->query("SET NAMES 'utf-8'");
$dbh->query($sql_index_items_table);

$dbh = null;
  

/**
 * DATABASE TRACKER
 */

$sql_hits_table = fc_generate_sql_query("fc_hits.php");
$sql_log_table = fc_generate_sql_query("fc_log.php");

$dbh = new PDO("sqlite:../$fc_db_stats");

$dbh->query($sql_hits_table);
$dbh->query($sql_log_table);

$dbh = null;


echo '<div class="alert alert-success">'.$lang['installed'].' | Admin: '.$username.'</div>';
echo '<hr><a class="btn" href="../acp/index.php">'.$lang['link_admin'].'</a><hr>';




?>