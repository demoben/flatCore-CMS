<?php
//prohibit unauthorized access
require 'core/access.php';

echo '<form id="editpage" action="acp.php?tn=pages&sub=edit" class="form-horizontal" method="POST">';

$custom_fields = get_custom_fields();
sort($custom_fields);
$cnt_custom_fields = count($custom_fields);


echo '<div class="row" style="margin-right:0;">';
echo '<div class="col-lg-9 col-md-8 col-sm-12">';

echo '<div class="card">';
echo '<div class="card-header">';

echo '<ul class="nav nav-tabs card-header-tabs" id="bsTabs" role="tablist">';
echo '<li class="nav-item"><a class="nav-link active" href="#info" data-toggle="tab">'.$lang['tab_info'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#content" data-toggle="tab">'.$lang['tab_content'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#extracontent" data-toggle="tab">'.$lang['tab_extracontent'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#meta" data-toggle="tab">'.$lang['tab_meta'].'</a></li>';

echo '<li class="nav-item ml-auto"><a class="nav-link" href="#posts" data-toggle="tab" title="'.$lang['tab_posts'].'">'.$icon['clipboard_list'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#addons" data-toggle="tab" title="'.$lang['tab_addons'].'">'.$icon['cogs'].'</a></li>';
echo '<li class="nav-item"><a class="nav-link" href="#head" data-toggle="tab" title="'.$lang['tab_head'].'">'.$icon['code'].'</a></li>';
if($cnt_custom_fields > 0) {
	echo '<li class="nav-item"><a class="nav-link" href="#custom" data-toggle="tab" title="'.$lang['legend_custom_fields'].'">'.$icon['th_list'].'</a></li>';
}



echo '</ul>';

echo '</div>';
echo '<div class="card-body">';

echo '<div class="tab-content">';

/* tab_info */
echo'<div class="tab-pane fade show active" id="info">';

$sql = "SELECT page_linkname, page_sort, page_title, page_language FROM fc_pages
		    WHERE page_sort != 'portal'
		    ORDER BY page_language ASC, page_sort ASC	";

$all_pages = $db_content->query($sql)->fetchAll();

$all_pages = fc_array_multisort($all_pages, 'page_language', SORT_ASC, 'page_sort', SORT_ASC, SORT_NATURAL);


$select_page_position  = '<select name="page_position" class="custom-select form-control">';
$select_page_position .= '<option value="null">' . $lang['legend_unstructured_pages'] . '</option>';


if($page_sort == "portal") {
	$select_page_position .= '<option value="portal" selected>' . $lang['f_homepage'] . '</option>';
} else {
	$select_page_position .= '<option value="portal">' . $lang['f_homepage'] . '</option>';
}

if(ctype_digit($page_sort)) {
	$select_page_position .= '<option value="mainpage" selected>'.$lang['f_mainpage'].'</option>';
} else {
	$select_page_position .= '<option value="mainpage">'.$lang['f_mainpage'].'</option>';
}

$select_page_position .= '<optgroup label="'.$lang['f_subpage'].'">';

for($i=0;$i<count($all_pages);$i++) {

	$selected = '';
	$disabled = '';
	
	if($all_pages[$i]['page_sort'] == $page_sort) {
		$disabled = 'disabled';
	}
	
	if($all_pages[$i]['page_sort'] == "") {
		continue;
	}
	
	if($pos = strripos($page_sort,".")) {
		$string = substr($page_sort,0,$pos);
	}
		 
	$parent_string = $all_pages[$i]['page_sort'];
	
	if($parent_string != "" && $parent_string == "$string") {
	 	$selected = "selected";
	}
		 
	$short_title = first_words($all_pages[$i]['page_title'], 6);
	$indent = str_repeat("-",substr_count($parent_string,'.'));
	$select_page_position .= "<option value='$parent_string' $selected $disabled> $indent " . $all_pages[$i]['page_sort'] . ' | ' .$all_pages[$i]['page_linkname'] . ' - ' . $short_title ."</option>";
	
}
$select_page_position .= '</optgroup>';
$select_page_position .= '</select>';



$page_order = substr (strrchr ($page_sort, "."), 1);
if(ctype_digit($page_sort)) {
	$page_order = $page_sort;
}
	
echo '<div class="row">';
echo '<div class="col-md-9">';
echo tpl_form_control_group('',$lang['f_page_position'],$select_page_position);
echo '</div>';
echo '<div class="col-md-3">';
echo tpl_form_control_group('',$lang['f_page_order'],"<input class='form-control' type='text' name='page_order' value='$page_order'>");
echo '</div>';
echo '</div>';

echo '<hr>';

echo '<div class="row">';
echo '<div class="col-md-9">';
echo tpl_form_control_group('',$lang['f_page_linkname'],'<input class="form-control" type="text" name="page_linkname" value="'.$page_linkname.'">');
echo '</div>';
echo '<div class="col-md-3">';
echo tpl_form_control_group('',$lang['f_page_hash'],"<input class='form-control' type='text' name='page_hash' value='$page_hash'>");
echo '</div>';
echo '</div>';


echo '<div class="form-group">';
echo '<label>'.$lang['f_page_permalink'].'</label>';
echo '<div class="input-group">';
echo '<div class="input-group-prepend">';
echo '<span class="input-group-text">'.$fc_base_url.'</span>';
echo '</div>';
echo '<input class="form-control" type="text" name="page_permalink" id="set_permalink" value="'.$page_permalink.'">';
echo '</div>';
echo '</div>';

?>
<script>
$(function() {
	var fc_base_url = "<? echo $fc_base_url; ?>";	
	$("#set_permalink").keyup(function(){
		var permalink = this.value;
		var check_url = fc_base_url.concat(permalink);
		$("a#check_link").attr("href", check_url);
		$("a#check_link").attr("title", check_url);
	});
});
</script>
<?php
	

echo '<div class="form-group">';
echo '<label>'.$lang['f_page_type_of_use'].'</label>';
$page_types = array('normal', 'register', 'profile', 'search', 'password', '404','display_post');
$select_page_type_of_use  = '<select name="page_type_of_use" class="custom-select form-control">';

foreach($page_types as $types) {
	$str = 'type_of_use_'.$types;
	$name = $lang[$str];
	$sel_page_type = '';
	if($page_type_of_use == $types) {
		$sel_page_type = 'selected';
	}
	
	$select_page_type_of_use .= '<option value="'.$types.'" '.$sel_page_type.'>'.$name.'</option>';
}


$select_page_type_of_use .= '</select>';

echo $select_page_type_of_use;

echo '</div>';
	

echo '<hr>';

/* redirect */

echo '<fieldset class="mt-4">';
echo '<legend>'.$lang['legend_redirect'].'</legend>';

/* shortlink */
if(empty($page_permalink_short_cnt)) {
	$page_permalink_short_cnt = 0;
}

echo '<div class="form-group">';
echo '<label>'.$lang['f_page_permalink_short'].'</label>';
echo '<div class="input-group">';
echo '<input class="form-control" type="text" name="page_permalink_short" value="'.$page_permalink_short.'">';
echo '<div class="input-group-append">';
echo '<span class="input-group-text">'.$page_permalink_short_cnt.'</span>';
echo '</div>';
echo '</div>';
echo '</div>';

/* funnel URI */
echo tpl_form_control_group('',$lang['f_page_funnel_uri'],'<textarea class="form-control" name="page_funnel_uri">'.$page_funnel_uri.'</textarea>');

$select_page_redirect_code  = '<select name="page_redirect_code" class="custom-select form-control">';
if($page_redirect_code == '') {
	$page_redirect_code = 301;
}
for($i=0;$i<10;$i++) {
	$redirect_code = 300+$i;
	unset($sel_page_redirect_code);
	if($page_redirect_code == $redirect_code) {
		$sel_page_redirect_code = 'selected';
	}
	$select_page_redirect_code .= '<option value="'.$redirect_code.'" '.$sel_page_redirect_code.'>'.$redirect_code.'</option>';
}
$select_page_redirect_code .= '</select>';

echo tpl_form_control_group('',$lang['f_page_redirect'],'<div class="row"><div class="col-md-3">'.$select_page_redirect_code.'</div><div class="col-md-9"><input class="form-control" type="text" name="page_redirect" value="'.$page_redirect.'"></div></div>');


echo '</fieldset>';

echo '<div class="clearfix"></div>';

echo '</div>'; /* EOL tab_info */


/* tab_content */
echo '<div class="tab-pane fade" id="content">';

echo '<textarea name="page_content" class="form-control mceEditor textEditor switchEditor" id="textEditor">'.$page_content.'</textarea>';

echo"</div>";
/* EOL tab_content */


/* tab_extracontent */

echo '<div class="tab-pane fade" id="extracontent">';

echo '<textarea name="page_extracontent" class="form-control mceEditor textEditor switchEditor" id="textEditor2">'.$page_extracontent.'</textarea>';

echo '</div>'; /* EOL tab_extracontent */



/* tab_meta */
echo '<div class="tab-pane fade" id="meta">';

echo '<div class="row">';
echo '<div class="col-md-6">';

echo tpl_form_control_group('',$lang['f_page_title'],'<input class="form-control" type="text" name="page_title" value="'.$page_title.'">');

if($prefs_publisher_mode == 'overwrite') {
	$page_meta_author = $prefs_default_publisher;
}

if($page_meta_author == "" && $prefs_default_publisher != '') {
	$page_meta_author = $prefs_default_publisher;
}

if($page_meta_author == "") {
	$page_meta_author = $_SESSION['user_firstname'] .' '. $_SESSION['user_lastname'];
}

echo tpl_form_control_group('',$lang['f_meta_author'],'<input class="form-control" type="text" name="page_meta_author" value="'.$page_meta_author.'">');
echo tpl_form_control_group('',$lang['f_meta_keywords'],'<input class="form-control" type="text" name="page_meta_keywords" value="'.$page_meta_keywords.'" data-role="tagsinput">');
echo tpl_form_control_group('',$lang['f_meta_description'],"<textarea name='page_meta_description' class='form-control cntValues' rows='5'>$page_meta_description</textarea>");

echo '</div>';
echo '<div class="col-md-6">';

echo '<div class="form-group">';
echo '<label>'.$lang['page_thumbnail'].'</label>';

if($prefs_pagethumbnail_prefix != '') {
	echo '<p>Prefix: '.$prefs_pagethumbnail_prefix.'</p>';
}

$images = fc_get_all_media_data('image');

$page_thumbnail_array = explode("&lt;-&gt;", $page_thumbnail);
$array_images = explode("<->", $post_data['post_images']);

$choose_images = fc_select_img_widget($images,$page_thumbnail_array,$prefs_pagethumbnail_prefix,1);
// picker1_images[]
echo $choose_images;

echo '</div>';

echo '</div>';
echo '</div>';

echo '</fieldset>';

$robots = array("all", "noindex", "nofollow", "none", "noarchive", "nosnippet", "noodp", "notranslate", "noimageindex");

$checkbox_robots = '<div class="btn-group btn-group-toggle" data-toggle="buttons">';
foreach($robots as $r) {
	
	$active = '';
	$checked = '';
	
	if(strpos($page_meta_robots, $r) !== false) {
		$active = 'active';
		$checked = 'checked';
	}
	
	$checkbox_robots .= '<label class="btn btn-fc btn-sm '.$active.'">';
	$checkbox_robots .= '<input type="checkbox" name="page_meta_robots[]" value="'.$r.'" '.$checked.'> '.$r;
	$checkbox_robots .= '</label>';
}
$checkbox_robots .= '</div>';

echo tpl_form_control_group('',$lang['f_meta_robots'],$checkbox_robots);

echo '</div>'; /* EOL tab_meta */



/* tab_head */
echo '<div class="tab-pane fade" id="head">';

echo $lang['f_head_styles'];
echo '<span class="silent"> &lt;style type=&quot;text/css&quot;&gt;</span> ... <span class="silent">&lt;/styles&gt;</span>';
echo '<textarea name="page_head_styles" class="form-control aceEditor_css" rows="12">'.$page_head_styles.'</textarea>';
echo '<div id="CSSeditor"></div>';

echo '<hr>';

echo $lang['f_head_enhanced'];
echo '<span class="silent"> &lt;head&gt;</span> ... <span class="silent">&lt;/head&gt;</span>';
echo '<textarea name="page_head_enhanced" class="form-control aceEditor_html" rows="12">'.$page_head_enhanced.'</textarea>';
echo '<div id="HTMLeditor"></div>';

echo '</div>'; /* EOL tab_head */

/* tab addons */
echo '<div class="tab-pane fade" id="addons">';

/* Select Modul */

$select_page_modul = '<select name="page_modul"  class="custom-select form-control">';
$select_page_modul .= '<option value="">Kein Modul</option>';

for($i=0;$i<count($all_mods);$i++) {

	$selected = "";
	$mod_name = $all_mods[$i]['name'];
	$mod_folder = $all_mods[$i]['folder'];

	if($mod_folder == $page_modul) {
		$selected = 'selected';
	}

	$select_page_modul .= "<option value='$mod_folder' $selected>$mod_name</option>";

}


$select_page_modul .= '</select>';


echo '<div class="form-group">';
echo '<label>'.$lang['f_page_modul'].'</label>';
echo $select_page_modul;
echo '</div>';

echo '<div class="form-group">';
echo '<label>'.$lang['f_page_modul_query'].'</label>';
echo "<input class='form-control' type='text' name='page_modul_query' value='$page_modul_query'>";
echo '</div>';

/* if there is */
if($page_modul != '') {
		/* check if the module has its own form */
		if(is_file("../modules/$page_modul/backend/page-form.php")) {
			include "../modules/$page_modul/backend/page-form.php";
		}
}


echo '</div>'; /* EOL tab addons */


echo '<div class="tab-pane fade" id="posts">';


echo '<fieldset>';
echo '<legend>'.$lang['categories'].'</legend>';

$categories = fc_get_categories();
$page_cats_array = explode(',', $page_posts_categories);

$checked_cat_all = '';
if(in_array('all', $page_cats_array)) {
	$checked_cat_all = 'checked';
}
	
echo '<div class="form-check">';
echo '<input type="checkbox" class="form-check-input" id="cat_all" name="page_post_categories[]" value="all" '.$checked_cat_all.'>';
echo '<label class="form-check-label" for="cat_all">'.$lang['label_all_categories'].'</label>';
echo '</div><hr>';


for($i=0;$i<count($categories);$i++) {
	
	$checked_cat = '';
	if(in_array($categories[$i]['cat_id'], $page_cats_array)) {
		$checked_cat = 'checked';
	}
	
	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="cat'.$i.'" name="page_post_categories[]" value="'.$categories[$i]['cat_id'].'" '.$checked_cat.'>';
	echo '<label class="form-check-label" for="cat'.$i.'">'.$categories[$i]['cat_name'].'</label>';
	echo '</div>';
}

echo '</fieldset>';

echo '<fieldset>';
echo '<legend>'.$lang['select_post_type'].'</legend>';

	if(strpos($page_posts_types, 'm') !== FALSE) {
		$check_m = 'checked';
	}
	if(strpos($page_posts_types, 'i') !== FALSE) {
		$check_i = 'checked';
	}
	if(strpos($page_posts_types, 'p') !== FALSE) {
		$check_p = 'checked';
	}
	if(strpos($page_posts_types, 'g') !== FALSE) {
		$check_g = 'checked';
	}
	if(strpos($page_posts_types, 'v') !== FALSE) {
		$check_v = 'checked';
	}
	if(strpos($page_posts_types, 'e') !== FALSE) {
		$check_e = 'checked';
	}
	if(strpos($page_posts_types, 'l') !== FALSE) {
		$check_l = 'checked';
	}

	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_m" name="page_post_types[]" value="m" '.$check_m.'>';
	echo '<label class="form-check-label" for="type_m">'.$lang['post_type_message'].'</label>';
	echo '</div>';
	
	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_i" name="page_post_types[]" value="i" '.$check_i.'>';
	echo '<label class="form-check-label" for="type_i">'.$lang['post_type_image'].'</label>';
	echo '</div>';
	
	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_g" name="page_post_types[]" value="g" '.$check_g.'>';
	echo '<label class="form-check-label" for="type_g">'.$lang['post_type_gallery'].'</label>';
	echo '</div>';
	
	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_v" name="page_post_types[]" value="v" '.$check_v.'>';
	echo '<label class="form-check-label" for="type_v">'.$lang['post_type_video'].'</label>';
	echo '</div>';
	
	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_p" name="page_post_types[]" value="p" '.$check_p.'>';
	echo '<label class="form-check-label" for="type_p">'.$lang['post_type_product'].'</label>';
	echo '</div>';
	
	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_l" name="page_post_types[]" value="l" '.$check_l.'>';
	echo '<label class="form-check-label" for="type_l">'.$lang['post_type_link'].'</label>';
	echo '</div>';
	
	echo '<hr>';

	echo '<div class="form-check">';
	echo '<input type="checkbox" class="form-check-input" id="type_e" name="page_post_types[]" value="e" '.$check_e.'>';
	echo '<label class="form-check-label" for="type_e">'.$lang['post_type_event'].'</label>';
	echo '</div>';


echo '</fieldset>';

echo '</div>'; /* EOL tab posts */

if($cnt_custom_fields > 0) {

/* tab custom fields */
echo '<div class="tab-pane fade" id="custom">';

	for($i=0;$i<$cnt_custom_fields;$i++) {
		
		$custom_field_value = '';
		$custom_field_value = ${$custom_fields[$i]};
		if(substr($custom_fields[$i],0,10) == "custom_one") {
			$label = substr($custom_fields[$i],11);
			echo tpl_form_control_group('',$label,'<input type="text" class="form-control" name="'.$custom_fields[$i].'" value="'.$custom_field_value.'">');
		}	elseif(substr($custom_fields[$i],0,11) == "custom_text") {
			$label = substr($custom_fields[$i],12);
			echo tpl_form_control_group('',$label,"<textarea class='form-control' rows='6' name='$custom_fields[$i]'>" .$custom_field_value. "</textarea>");
		}	elseif(substr($custom_fields[$i],0,14) == "custom_wysiwyg") {
			$label = substr($custom_fields[$i],15);
			echo tpl_form_control_group('',$label,"<textarea class='mceEditor_small' name='$custom_fields[$i]'>" .$custom_field_value. "</textarea>");
		}		
	}

echo '</div>'; /* EOL tab custom fields */

}

echo '</div>';

echo '</div>';
echo '</div>';


echo '</div>';
echo '<div class="col-lg-3 col-md-4 col-sm-12">';


echo '<div class="card">';
echo '<div class="card-header">'.$lang['tab_page_preferences'].'</div>';
echo '<div class="card-body" style="padding-left:30px;padding-right:30px;">';


echo '<div class="form-group">';
echo '<div class="btn-group btn-group-toggle d-flex" data-toggle="buttons" role="flex">';
echo '<label class="btn btn-sm btn-fc w-100"><input type="radio" name="optEditor" value="optE1"> WYSIWYG</label>';
echo '<label class="btn btn-sm btn-fc w-100"><input type="radio" name="optEditor" value="optE2"> Text</label>';
echo '<label class="btn btn-sm btn-fc w-100"><input type="radio" name="optEditor" value="optE3"> Code</label>';
echo '</div>';
echo '</div>';


/* Select Language */
$arr_lang = get_all_languages();

if($page_language == '' && $prefs_default_language != '') {
	$page_language = $prefs_default_language;
}

$select_page_language  = '<select name="page_language" class="custom-select form-control">';
for($i=0;$i<count($arr_lang);$i++) {

	$lang_sign = $arr_lang[$i]['lang_sign'];
	$lang_desc = $arr_lang[$i]['lang_desc'];
	$lang_folder = $arr_lang[$i]['lang_folder'];
	$select_page_language .= "<option value='$lang_folder'".($page_language == "$lang_folder" ? 'selected="selected"' :'').">$lang_sign ($lang_desc)</option>";	

} // eo $i

$select_page_language .= '</select>';

echo '<div class="form-group">';
echo '<label>'.$lang['f_page_language'].'</label>';
echo $select_page_language;
echo '</div>';


/* Select Template */

$arr_Styles = get_all_templates();

$select_select_template = '<select id="select_template" name="select_template"  class="custom-select form-control">';

if($page_template == '') {
	$selected_standard = 'selected';
}

$select_select_template .= "<option value='use_standard<|-|>use_standard' $selected_standard>$lang[use_standard]</option>";

/* templates list */
foreach($arr_Styles as $template) {

	$arr_layout_tpl = glob("../styles/$template/templates/layout*.tpl");
	
	$select_select_template .= "<optgroup label='$template'>";
	
	foreach($arr_layout_tpl as $layout_tpl) {
		$layout_tpl = basename($layout_tpl);
	
		$selected = '';
		if($template == "$page_template" && $layout_tpl == "$page_template_layout") {
			$selected = 'selected';
		}
		
		$select_select_template .=  "<option $selected value='$template<|-|>$layout_tpl'>$template » $layout_tpl</option>";
	}
	
	$select_select_template .= '</optgroup>';

}

$select_select_template .= '</select>';

echo '<div class="form-group">';
echo '<label>'.$lang['f_page_template'].'</label>';
echo $select_select_template;
echo '</div>';



/* Select  Status */

unset($checked_status);

if($page_status == "") {
	$page_status = "public";
}


$select_page_status = '<div class="btn-group btn-group-vertical btn-group-toggle d-flex" data-toggle="buttons" role="group">';

$select_page_status .= '<label class="btn btn-sm btn-fc w-100 btn-public '.($page_status == "public" ? 'active' :'').' ">';
$select_page_status .= "<input type='radio' name='page_status' value='public'".($page_status == "public" ? 'checked' :'')."> $lang[f_page_status_puplic]";
$select_page_status .= '</label>';

$select_page_status .= '<label class="btn btn-sm btn-fc w-100 btn-ghost '.($page_status == "ghost" ? 'active' :'').'">';
$select_page_status .= "<input type='radio' name='page_status' value='ghost'".($page_status == "ghost" ? 'checked' :'')."> $lang[f_page_status_ghost]";
$select_page_status .= '</label>';

$select_page_status .= '<label class="btn btn-sm btn-fc w-100 btn-private '.($page_status == "private" ? 'active' :'').'">';
$select_page_status .= "<input type='radio' name='page_status' value='private'".($page_status == "private" ? 'checked' :'')."> $lang[f_page_status_private]";
$select_page_status .= '</label>';

$select_page_status .= '<label class="btn btn-sm btn-fc w-100 btn-draft '.($page_status == "draft" ? 'active' :'').'">';
$select_page_status .= "<input type='radio' name='page_status' value='draft'".($page_status == "draft" ? 'checked' :'')."> $lang[f_page_status_draft]";	
$select_page_status .= '</label>';

$select_page_status .= '</div>';


echo '<div class="form-group">';
echo '<label>'.$lang['f_page_status'].'</label>';
echo '<div>';
echo $select_page_status;
echo '</div>';
echo '</div>';


/* set or reset password */

echo '<div class="form-group">';
echo '<label>'.$lang['label_password'].'</label>';
$placeholder = '';
if($page_psw != '') {
	echo '<input type="hidden" name="page_psw_relay" value="'.$page_psw.'">';
	$placeholder = '*****';
}
echo '<input class="form-control" type="text" name="page_psw" value="" placeholder="'.$placeholder.'">';

echo '<div class="checkbox"><label>';
echo '<input type="checkbox" name="page_psw_reset" value="reset"> '.$lang['label_password_reset'];
echo '</label></div>';

echo '</div>';


/* Select Usergroups */

$arr_groups = get_all_groups();
$arr_checked_groups = explode(",",$page_usergroup);

for($i=0;$i<count($arr_groups);$i++) {

	$group_id = $arr_groups[$i]['group_id'];
	$group_name = $arr_groups[$i]['group_name'];

	if(in_array("$group_name", $arr_checked_groups)) {
		$checked = "checked";
	} else {
		$checked = "";
	}
	
	$checkbox_usergroup .= '<div class="checkbox"><label>';
	$checkbox_usergroup .= "<input type='checkbox' $checked name='set_usergroup[]' value='$group_name'> $group_name";
	$checkbox_usergroup .= '</label></div>';
}

echo '<div class="form-group">';
echo '<div class="well well-sm">';
echo '<a href="#usergroups" data-toggle="collapse" data-target="#usergroups">'.$lang['legend_choose_group'].'</a>';
echo '<div id="usergroups" class="collapse p-3">';
echo $checkbox_usergroup;
echo '</div>';
echo '</div>';


/* Select Rights Management */

$arr_admins = get_all_admins();
$arr_checked_admins = explode(",", $page_authorized_users);
$cnt_admins = count($arr_admins);

for($i=0;$i<$cnt_admins;$i++) {

	$user_nick = $arr_admins[$i]['user_nick'];

  if(in_array("$user_nick", $arr_checked_admins)) {
		$checked_user = "checked";
	} else {
		$checked_user = "";
	}
		
	$checkbox_set_authorized_admins .= '<div class="checkbox"><label>';
 	$checkbox_set_authorized_admins .= "<input type='checkbox' $checked_user name='set_authorized_admins[]' value='$user_nick'> $user_nick";
 	$checkbox_set_authorized_admins .= '</label></div>';
}


echo '<div class="well well-sm">';
echo '<a href="#admins" data-toggle="collapse" data-target="#admins">'.$lang['f_page_authorized_admins'].'</a>';
echo '<div id="admins" class="collapse p-3">';
echo $checkbox_set_authorized_admins;
echo '</div>';
echo '</div>';



/* select labels */


$cnt_labels = count($fc_labels);
$arr_checked_labels = explode(",", $page_labels);

for($i=0;$i<$cnt_labels;$i++) {
	$label_title = $fc_labels[$i]['label_title'];
	$label_id = $fc_labels[$i]['label_id'];
	$label_color = $fc_labels[$i]['label_color'];
	
  if(in_array("$label_id", $arr_checked_labels)) {
		$checked_label = "checked";
	} else {
		$checked_label = "";
	}
	
	$checkbox_set_labels .= '<div class="checkbox"><label>';
 	$checkbox_set_labels .= "<input type='checkbox' $checked_label name='set_page_labels[]' value='$label_id'> $label_title";
 	$checkbox_set_labels .= '</label></div>';
	
}


echo '<div class="well well-sm">';
echo '<a href="#labels" data-toggle="collapse" data-target="#labels">'.$lang['labels'].'</a>';
echo '<div id="labels" class="collapse p-3">';
echo $checkbox_set_labels;
echo '</div>';
echo '</div>';

echo '</div>'; // form-group



echo '<input type="hidden" name="page_version" value="'.$page_version.'">';
echo '<input type="hidden" name="modus" value="'.$modus.'">';

echo '<div class="form-group">';
echo $submit_button;
echo '<div class="btn-group d-flex mt-2">';
echo $previev_button.' '.$delete_button;
echo '</div>';
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
if(is_numeric($editpage)) {
	echo '<input type="hidden" name="editpage" value="'.$editpage.'">';
}
echo '</div>';

echo '</div>'; // panel-body
echo '</div>'; // panel

echo '</div>'; // col
echo '</div>'; // row


//submit form to save data



echo '</form>';



?>