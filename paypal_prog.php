<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-synch';

foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// If possible, securely post back to paypal using HTTPS
// Your PHP server will need to be SSL enabled
// $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// assign posted variables to local variables
$custom = explode('|',$_POST['custom']);
$pay_txn_id = $custom['pay_txn_id'];
$paid_thru = $custom['paid_thru'];
$mode = $custom['mode'];
$invoice = $_POST['invoice'];
$receiver_email = $_POST['receiver_email'];
$item_name = $_POST['item_name'];
$item_number = $_POST['item_number'];
$quantity = $_POST['quantity'];
$payment_status = $_POST['payment_status'];
$pending_reason = $_POST['pending_reason'];
$payment_date = $_POST['payment_date'];
$total = $_POST['payment_gross'];
$payment_fee = $_POST['payment_fee'];
$txn_id = $_POST['txn_id'];
$txn_type = $_POST['txn_type'];
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$address_street = $_POST['address_street'];
$address_city = $_POST['address_city'];
$address_state = $_POST['address_state'];
$address_zip = $_POST['address_zip'];
$address_country = $_POST['address_country'];
$address_status = $_POST['address_status'];
$email = $_POST['payer_email'];
$payer_status = $_POST['payer_status'];
$payment_type = $_POST['payment_type'];
$notify_version = $_POST['notify_version'];
$verify_sign = $_POST['verify_sign'];
$user_id = $_SESSION['UserId'];

// post back to PayPal system to validate
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
if ($mode == 'test') {
	$fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
} else {
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
}
if (!$fp) {
// HTTP ERROR
} else {
	fputs ($fp, $header . $req);

	// read the body data
	$res = '';
	while (!feof($fp)) {
		$res = fgets ($fp, 1024);
	}

	$params = array();

	$params['payment_status'] = $payment_status;
	$params['pay_txn_id'] = $pay_txn_id;
	$params['email'] = $email;
	$params['txn_id'] = $txn_id;
	$params['amount'] = $total;
	$params['valid'] = true;
	$params['vars'] = addslashes(serialize($_REQUEST));

	$x = process_payment_info($params);

	fclose ($fp);
}

?>