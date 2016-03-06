<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

$oldpwd = trim( $_POST['txtoldpwd'] );
$newpwd = trim( $_POST['txtnewpwd'] );
$conpwd = trim( $_POST['txtconpwd'] );

if ( $newpwd == $conpwd ) {

	if (strtolower($_POST['spam_code']) != strtolower($_SESSION['spam_code'])) {

		header( "location: changempass.php?errid=121" );

		exit;
	}

	$sql = 'SELECT id FROM ! WHERE id = ? AND password = ?';

	$exists = $db->getOne( $sql, array( USER_TABLE, $_SESSION['UserId'], md5( $oldpwd ) ) );

	if ( $exists ) {

		$newpwd = md5( $newpwd );

		$sql = 'update ! set password = ? where id = ?';

		$db->query( $sql, array( USER_TABLE, $newpwd, $_SESSION['UserId'] ) );

		if ($config['forum_installed'] != '' && $config['forum_installed'] != 'None') {

		    include_once($config['forum_installed'] . '_forum.php');

			forum_modifympass($conpwd);

		}

		$t->assign('rendered_page', $t->fetch('pwdchanged.tpl') );

		$t->display( 'index.tpl' );

		exit;

	} else {

		header( "location: changempass.php?errid=".WRONG_OLD_PASSWORD );

	}

} else {

	header( "location: changempass.php?errid=".PASS_CONFIRMPASS );

}

?>