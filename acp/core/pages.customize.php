<?php

/**
 * Add and remove custom fields to the to the table "fc_pages"
 *
 * @author	Patrick Konstandin
 * @since		29.05.2012
 * @todo		add workaround for the missing SQLite-Feature DROP COLUMN
 */

//prohibit unauthorized access
require("core/access.php");


/**
 * Delete Custom Fields
 * NO SQLITE SUPPORT FOR THE MOMENT
 * @todo: find a workaround
 */
 
if($_POST['delete_field']) {
	$del_field = strip_tags($_POST['del_field']);
	
	if(substr($del_field,0,7) == "custom_") {
				
		//$dbh = new PDO("sqlite:".CONTENT_DB);
		$sql = "ALTER TABLE fc_pages DROP COLUMN $del_field";
		$db_content->query($sql);

		$sql = "ALTER TABLE fc_pages_cache DROP COLUMN $del_field";
		$db_content->query($sql);		
		
		//$cnt_changes = $dbh->exec($sql);
		//$dbh = null;
		
		if($cnt_changes > 0) {
			$sys_message = "{OKAY} $lang[db_changed]";
			record_log("$_SESSION[user_nick]","delete column $del_field","0");
		} else {
			$sys_message = "{error} $lang[db_not_changed]";
		}
		print_sysmsg("$sys_message");
	}
}



/**
 * Add new Custom Column
 */

if($_POST['add_field']) {
	
	$col = clean_vars($_POST['field_name']);
	if($col == "") {
		/* if there is no name given, we use the timestamp */
		$col = time();
	}
	
	switch($_POST['field_type']) {
		case 'one':
			$type = "one";
			break;
		case 'text':
			$type = "text";
			break;
		case 'wysiwyg':
			$type = "wysiwyg";
			break;
		default:
			$type = "one"; 
	}
	
	$new_col = "custom_" . $type . "_" . $col;
	
	//$dbh = new PDO("sqlite:".CONTENT_DB);
	//$sql = "SELECT * FROM fc_pages";
	//$result = $dbh->query($sql)->fetch(PDO::FETCH_ASSOC);
	//$result = $db_content->query($sql)->fetch(PDO::FETCH_ASSOC);
	$result = $db_content->select("fc_pages", "*");
	
	/* if not exists, create column */
	if(!array_key_exists("$new_col", $result)) {
	   	$sql = "ALTER TABLE fc_pages ADD $new_col LONGTEXT";
	   	//$dbh->exec($sql);
	   	$db_content->query($sql);
	   	
	   	$sql = "ALTER TABLE fc_pages_cache ADD $new_col LONGTEXT";
	   	//$dbh->exec($sql);
	   	$db_content->query($sql);
	   	
	   	record_log("$_SESSION[user_nick]","add custom column <i>$new_col</i>","0");
	   	print_sysmsg("{OKAY} $lang[db_changed]"); 	
   }
	
	//$dbh = null;
}


echo '<div class="row"><div class="col-md-12">';


echo '<fieldset>';
echo '<legend>' . $lang['add_custom_field'] . '</legend>';

echo '<form action="acp.php?tn=pages&sub=customize" method="POST" class="form-horizontal">';

echo tpl_form_control_group('',$lang['custom_field_name'],"<input type='text' class='form-control' name='field_name' value='$field_name'>");

$radio_field_type = "
			<label class='radio inline'><input type='radio' $sel1 name='field_type' value='one'> &lt;input type=&quot;text&quot; ... </label>
			<label class='radio inline'><input type='radio' $sel2 name='field_type' value='text'> &lt;textarea ... </label>
			<label class='radio inline'><input type='radio' $sel3 name='field_type' value='wysiwyg'> &lt;textarea ... (WYSIWYG)</label>";

echo tpl_form_control_group('','',$radio_field_type);

echo '<hr>';

echo"<input type='submit' class='btn btn-save' name='add_field' value='$lang[save]'>";
echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';


echo '</form>';
echo '</fieldset>';



/**
 * Show custom columns
 */


echo '<fieldset>';
echo '<legend>' . $lang['delete_custom_field'] . '</legend>';

echo '<form action="acp.php?tn=pages&sub=customize" class="form-horizontal" method="POST">';

$result = get_custom_fields();
$cnt_result = count($result);


if($cnt_result < 1) {
	echo '<div class="alert alert-info">' . $lang['no_custom_fields'] . '</div>';
} else {
	echo '<div class="alert alert-danger">' . $lang['delete_custom_field_desc'] . '</div>';

	echo '<table class="table table-condensed">';
	
	for($i=0;$i<$cnt_result;$i++) {
		if(substr($result[$i],0,7) == "custom_") {
			
			$this_name = $result[$i];
			$this_name_smarty = '{$'.$this_name.'}';
			
			echo '<tr>';
			echo '<td>'.$this_name.'</td>';
			echo '<td><code>'.$this_name_smarty.'</code></td>';
			echo '<td>';
			echo '<form action="acp.php?tn=pages&sub=customize" class="form-inline" method="POST">';
			echo '<input type="hidden" name="del_field" value="'.$result[$i].'">';
			echo '<button type="submit" class="btn btn-sm btn-fc btn-block text-danger" name="delete_field">'.$icon['trash_alt'].'</button>';
			echo '<input  type="hidden" name="csrf_token" value="'.$_SESSION['token'].'">';
			echo '</form>';
			echo '</td>';
			echo '</tr>';
		}
	}
	
	echo '</table>';
	
}

echo '</form>';
echo '</fieldset>';
echo '</div></div>';


?>