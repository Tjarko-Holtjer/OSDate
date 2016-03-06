<?php

require_once(dirname(__FILE__).'/init.php');

if (!isset($_REQUEST['a']) || empty($_REQUEST['a']) ) return '';

switch (trim($_REQUEST['a'])) {

	case 'getUsers':

		$text = str_replace('|amp|','&amp;',strip_tags($_REQUEST['msg']));

		$sql = 'select username, firstname, lastname from ! where username like ? order by username';

		$users = $db->getAll( $sql, array( USER_TABLE, '%'.$text.'%' ) );

		$ret = '<select name="reqdusers" id="reqdusers"  multiple style="width: 90px;">';
		foreach ($users as $user) {
			$ret.='<option value="'.$user['username'].'">'.$user['firstname'].' '.$user['lastname'].'</option>';
		}
		$ret.='</select>&nbsp;';
		$ret.='&nbsp;<input type="button" value="'.get_lang('ok').'" class="formbutton" onclick="selectedUsers();" />';

		echo '|||usernameFind|:|'.$ret;
		break;

	default : return ''; break;
}

?>
