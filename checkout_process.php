<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if (!isset($pay_txn_id) or $pay_txn_id == '') {$pay_txn_id = $_REQUEST['pay_txn_id']; }

if (!isset($paid_thru) or $paid_thru == '') {$paid_thru = $_REQUEST['paid_thru']; }

if (isset($_REQUEST['rtnlink']) ) { $rtnlink = $_REQUEST['rtnlink']; }

if (isset($_REQUEST['payment_cancel']) && $_REQUEST['payment_cancel'] == 1) {
	/* Payment processing cancelled by user.  */

	$sql = 'update ! set payment_status=? where id = ?';

	$db->query($sql, array(TRANSACTIONS_TABLE,'Cancelled', $pay_txn_id) );

	$t->assign('error_msg', '1');

	$t->assign('rendered_page', $t->fetch('checkout_process.tpl') );

	$t->display( 'index.tpl' );

	exit;

}

$valid = false;

if ( $paid_thru == 'pm2checkout') { // 2CHECKOUT.COM
	$pay_txn_id = $_REQUEST['cart_order_id'];
	$txn_id			= $_REQUEST['order_number'];
	$email			= 'Credit Card';

	// check to see if payment is pending
	if ( $_REQUEST['credit_card_processed'] == 'Y' ) {
		$payment_status = 'Completed';
	} else {
		$payment_status = 'Declined';
	}
	$valid = true;

} else if ($paid_thru == 'paypal') {

	$payment_status = $_REQUEST['payment_status'];
	$pay_txn_id = $_REQUEST['pay_txn_id'];
	$email = $_REQUEST['payer_email'];
	$txn_id = $_REQUEST['txn_id'];
	$amount = $_REQUEST['payment_gross'];
	$valid = true;
	$vars = addslashes(serialize($_REQUEST));


} else if ( $paid_thru == 'egold') {

	$pay_txn_id = $_REQUEST['pay_txn_id'];
	$txn_id			= $_POST['PAYMENT_BATCH_NUM'];
	$total			= $_POST['PAYMENT_AMOUNT'];
	$email 			= $_POST['PAYER_ACCOUNT'];
	$payment_status = 'Completed';
	$valid = true; // e-gold payment are always instant, never pending

} else if ($paid_thru == 'free') {

	$pay_txn_id = $_REQUEST['pay_txn_id'];
	$txn_id			= $pay_txn_id;
	$total			= 0;
	$payment_status = 'Completed';
	$valid = true;

}

if ($paid_thru == 'pm2checkout' or $paid_thru == 'egold' or $paid_thru = 'paypal' or $paid_thru == 'free') {

	$vars = addslashes( serialize( $_POST ) );

	$params = array();

	$params['pay_txn_id'] = $pay_txn_id;
	$params['paid_thru'] = $paid_thru;
	$params['txn_id'] = $txn_id;
	$params['amount'] = $total;
	$params['payment_status'] = $payment_status;
	$params['valid'] = $valid;
	$params['email'] = $email;
	$params['vars'] = $vars;

	$level_name = process_payment_info($params);
}

$levels =$db->getRow('select mem.name, trn.payment_status from ! as trn, ! as mem where trn.id = ? and mem.roleid = trn.to_membership', array(TRANSACTIONS_TABLE, MEMBERSHIP_TABLE, $pay_txn_id) );

$t->assign ( 'level', $levels['name'] .' - Status: '.$levels['payment_status']);

$_SESSION['security'] = '';

hasRight('');

$t->assign('rendered_page', $t->fetch('checkout_process.tpl') );

$t->display( 'index.tpl' );

exit;
?>