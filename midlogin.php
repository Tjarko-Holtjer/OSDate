<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

// to do: change error codes to PHP constants

if ( $_SESSION['txtusername'] != '' && !$_POST['txtusername'] ) {

	$_POST['txtusername'] = $_SESSION['txtusername'];
	$_POST['txtpassword'] = base64_decode( $_SESSION['txtpassword'] );
	$_POST['rememberme']  = $_SESSION['rememberme'];
}

if ( $_POST['txtusername'] == '' ) {

	$err = USERNAME_BLANK;

} elseif( $_POST['txtpassword'] == '' ){

	$err = PASSWORD_BLANK;

} else {

	$get_params = $_REQUEST['get_params'];

	if ($get_params != '') {

		$get_params = unserialize(stripslashes($get_params));
		$gp = '';
		foreach($get_params as $k => $v) {
			if ($gp != '') {$gp .= '&';}
			$gp .= $k .'='.$v;
		}
	}

	$pwd = md5( trim( $_POST['txtpassword'] ) );

	$sql = 'SELECT id, username, firstname, lastname, regdate, level, status, email,  lastvisit, levelend, active, gender  FROM ! where username = ? and password = ? and status not in (?, ?, ?, ?)';

	$row = $db->getRow( $sql, array( USER_TABLE, $_POST['txtusername'], $pwd, get_lang('status_enum','rejected'), 'rejected', get_lang('status_enum','suspended'), 'suspended' ) );

	if( $row['id'] > 0 ) {

		$opt_lang=$_SESSION['opt_lang'];

		session_destroy();

		session_start();

		$_SESSION['opt_lang'] = $opt_lang;

		$_SESSION['UserId'] = $row['id'];

		$_SESSION['FullName'] = $row['firstname'] . ' ' . $row['lastname'];

		$_SESSION['UserName'] = $row['username'];

		if ($row['active'] == '1') {
			$cookie['username'] = $row['username'];

			if ($_POST['rememberme']) {
				$cookie['dir'] = base64_encode($_POST['txtpassword']);
				setcookie($config['cookie_prefix']."osdate_info[username]", $cookie['username'], strtotime("+30day"), "/" );
				setcookie($config['cookie_prefix']."osdate_info[dir]", $cookie['dir'], strtotime("+30day"), "/" );
			} else {
				setcookie($config['cookie_prefix']."osdate_info[username]", $cookie['username'], strtotime("-1day"), "/" );
				setcookie($config['cookie_prefix']."osdate_info[dir]", $cookie['dir'], strtotime("-1day"), "/" );
			}


			$_SESSION['FirstName'] = $row['firstname'];

			$_SESSION['gender'] = $row['gender'];

			$_SESSION['regdate'] = $row['regdate'];

			$_SESSION['whatIneed'] = base64_encode($_POST['txtpassword']);

			if (date('Ymd',$row['levelend']) < date('Ymd')) {

				$_SESSION['RoleId'] = $row['level'] = $config['expired_user_level'];

				$_SESSION['expired'] = 1;

			} elseif( $row['status'] == get_lang('status_enum','approval') || $row['status'] == 'approval' ){

				$_SESSION['RoleId'] = $config['default_user_level'];

/*			} elseif( $row['active'] != '1' ){

				$_SESSION['RoleId'] = $config['default_user_level'];  */

			} else {

				$_SESSION['RoleId'] = $row['level'];
			}

			$_SESSION['active'] = $row['active'];

			$_SESSION['lastvisit'] = $row['lastvisit'];

			$_SESSION['status'] = $row['status'];

			$_SESSION['email'] = $row['email'];


		/* Now add the settings of this user to session */
			$recs = $db->getAll('select * from ! where userid = ?',  array(USER_CHOICES_TABLE, $_SESSION['UserId']));

			if (count($recs) > 0) {

				$t->assign('act','modify');

				foreach ($recs as $rec) {

					$user_choices[$rec['choice_name']] = $rec['choice_value'];
				}
			}

			$_SESSION['mysettings'] = $user_choices;

		/* mysettings is set */

			$sql = 'DELETE FROM ! WHERE userid = ?';

			$db->query( $sql, array( ONLINE_USERS_TABLE, $_SESSION['UserId'] ) );

			$sql = 'insert into ! ( userid, lastactivitytime, is_online) values ( ?, ?,? )';

			$visittime=time();

			$db->query( $sql, array( ONLINE_USERS_TABLE, $_SESSION['UserId'], $visittime ,'1') );

			$sql = "UPDATE ! SET lastvisit=? WHERE id=?";

			$db->query( $sql ,array( USER_TABLE, $visittime,$_SESSION['UserId'] ) );

			hasRight('');
		}

		if ( $row['active'] != '1') {

			$err = NOT_ACTIVE;
			header( 'location: index.php?errid=' . $err );
			exit();

		}
		if ( $row['status'] == get_lang('status_enum','approval') or $row['status'] == 'approval' ) {

			$err = NOT_YET_APPROVED;
			header( 'location: index.php?errid=' . $err );
			exit();

		}

		if (isset($_REQUEST['returnto']) && $_REQUEST['returnto'] != '') {
			header('location: '.$_REQUEST['returnto'].'?'.$gp);
			exit();
		}

		header('location: index.php');
		exit();


	} else {

		$err = INVALID_USERNAME;

		setcookie("osdate_info", '', strtotime("+30day"), "/" );

		if (isset($_REQUEST['returnto']) && $_REQUEST['returnto'] != '') {
			header('location: index.php?page=login&errid=' . $err.'&returnto='.$_REQUEST['returnto'].'&get_params='.(serialize($gp)));
			exit();
		} else {
			header( 'location: index.php?page=login&errid=' . $err );
			exit();
		}
	}

}
?>