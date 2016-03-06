<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

$sql = 'SELECT roleid, name, price, currency FROM ! WHERE roleid = ?';

$row = $db->getRow( $sql, array( MEMBERSHIP_TABLE, $_REQUEST['membership'] ) );

$t->assign( 'item_no', $row['roleid'] );
$t->assign( 'item_name', $row['name'] );
$t->assign( 'amount', $row['price'] );
$t->assign( 'currency', $row['currency'] );

$currency = $row['currency'];

if ($row['price'] == '0') $payment = 'free';

$amount = $row['price'];

/* Now create a record in the database for this transwaction with status 'Started'  */

$invoice_no = $_SESSION['UserId']."-".time();

$_SESSION['invoice_no'] = $invoice_no;

$t->assign('invoice_no', $invoice_no);

$sql = 'insert into ! (user_id, invoice_no, from_membership, to_membership, amount_paid, txn_date, payment_mod, payment_status ) values (?, ?, ?, ?, ?, ?, ?, ?)';

$db->query($sql, array(TRANSACTIONS_TABLE, $_SESSION['UserId'], $invoice_no, $_SESSION['RoleId'], $_POST['membership'], $row['price'], date('Ymd'), $payment, 'Started'));

$_SESSION['pay_txn_id'] = $db->getOne('select id from ! where invoice_no = ?',array(TRANSACTIONS_TABLE, $invoice_no));

$t->assign('pay_txn_id',$_SESSION['pay_txn_id']);

if ( strtolower( $_REQUEST['payment'] ) == 'free' ) {

	if ( $row['price'] == 0 ) {

		$t->assign('rendered_page', $t->fetch('free_checkout.tpl') );

		$t->display( 'index.tpl' );

	} else {

		header( 'location: payment.php?err=' . get_lang('mship_errors',4) );

		exit;
	}
}
else {
	$payment = $_POST['payment'];

	if( $payment == '' ) {

		header( 'location: payment.php?err=' . get_lang('signup_js_errors','select_payment') );

		exit;
	}

	require( 'modules/payment/'.$payment.'.php');

  	$paymod = new $payment;

	$paymod->process_button();

	$t->display( 'index.tpl' );

}

?>
