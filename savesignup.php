<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}


$_SESSION['firstname'] = $firstname = addslashes(stripEmails(strip_tags(trim($_POST[ 'txtfirstname' ]))));

$_SESSION['lastname'] = $lastname = addslashes(stripEmails(strip_tags(trim($_POST[ 'txtlastname' ]))));

$_SESSION['username'] = $username = addslashes(stripEmails(strip_tags(trim($_POST[ 'txtusername' ]))));

$_SESSION['about_me'] = $about_me = addslashes(stripEmails(strip_tags(trim($_POST[ 'about_me' ]))));

$password = strip_tags(trim($_POST[ 'txtpassword' ]));

$password2 = strip_tags(trim($_POST[ 'txtpassword2' ]));

$_SESSION['password'] = $password;

$_SESSION['password2'] = $password2;

$_SESSION['email'] = $email = strip_tags(trim($_POST[ 'txtemail' ]));

$_SESSION['gender'] = $gender = trim($_POST[ 'txtgender' ]);

$birthmonth = trim($_POST[ 'txtbirthMonth' ]);

$birthday = trim($_POST[ 'txtbirthDay' ]);

$birthyear = trim($_POST[ 'txtbirthYear' ]);

$birthdate = $birthyear.'-'.$birthmonth.'-'.$birthday;

$_SESSION['selectedtime'] = @strtotime($birthdate);

$_SESSION['timezone'] = $timezone = trim($_POST[ 'txttimezone' ]);

if (!($timezone) or $timezone == '') $_SESSION['timezone'] = $timezone = 0;

$_SESSION['lookgender'] = $lookgender = trim($_POST[ 'txtlookgender' ]);

// note: this is named txtlook.. to avoid conflict with the lookagestart and lookageend from init.php

$_SESSION['txtlookagestart'] = $lookagestart = (trim($_POST[ 'txtlookagestart' ])!='')?trim($_POST[ 'txtlookagestart' ]):0;
$_SESSION['txtlookageend'] = $lookageend = (trim($_POST[ 'txtlookageend' ])!='')?trim($_POST[ 'txtlookageend' ]):0;

$_SESSION['from'] = $from = trim($_POST[ 'txtfrom' ]);

$_SESSION['address1'] = $address1 = addslashes(stripEmails(strip_tags(trim($_POST['txtaddress1' ]))));

$_SESSION['address2'] = $address2 = addslashes(stripEmails(strip_tags(trim($_POST['txtaddress2' ]))));

$_SESSION['stateprovince'] = $stateprovince = trim($_POST[ 'txtstateprovince' ])=='-1'?'AA':trim(addslashes(strip_tags($_POST[ 'txtstateprovince' ])));

$_SESSION['countycode'] = $county = (trim($_POST[ 'txtcounty' ])=='-1')?'AA':trim(addslashes(strip_tags($_POST[ 'txtcounty' ])));

$_SESSION['citycode'] = $city = (trim($_POST[ 'txtcity' ])=='-1')?'AA':trim(addslashes(strip_tags($_POST[ 'txtcity' ])));

$_SESSION['zip'] = $zip = trim(addslashes(strip_tags($_POST[ 'txtzip' ])));

$_SESSION['lookfrom'] = $lookfrom = trim($_POST[ 'txtlookfrom' ]);

$_SESSION['lookstateprovince'] = $lookstateprovince = (trim($_POST[ 'txtlookstateprovince' ])=='-1')?'AA':trim(addslashes(strip_tags($_POST[ 'txtlookstateprovince' ])));

$_SESSION['lookcounty'] = $lookcounty = (trim($_POST[ 'txtlookcounty' ])=='-1')?'AA':trim(strip_tags($_POST[ 'txtlookcounty' ]));

$_SESSION['lookcity'] = $lookcity = (trim($_POST[ 'txtlookcity' ])=='-1')?'AA':trim(addslashes(strip_tags($_POST[ 'txtlookcity' ])));

$_SESSION['lookzip'] = $lookzip = trim(addslashes(strip_tags($_POST[ 'txtlookzip' ])));

$_SESSION['lookradius'] = $lookradius = trim(addslashes(strip_tags($_POST[ 'lookradius' ])));

$_SESSION['radiustype'] = $radiustype = trim($_POST[ 'radiustype' ]);

$_SESSION['viewonline'] = $viewonline = trim($_POST[ 'txtviewonline' ]);

$_SESSION['couple_usernames'] = $couple_usernames = (isset($_POST['couple_usernames']))?addslashes(strip_tags(trim($_POST[ 'couple_usernames' ]))):'';


if ($viewonline == '' or !($viewonline)) $_SESSION['viewonline'] = $viewonline = 1;

/*
if (  $_POST['chgcntry'] == '1'   ) {
	header ( "location: signup.php" );
	exit();
}
*/

//Check for duplicate user
$sqlc = 'SELECT count(*) as aacount from ! where username = ?';

$rowc = $db->getRow( $sqlc, array( USER_TABLE, $username ) );

$rowd = $db->getRow( $sqlc, array( ADMIN_TABLE, $username )  );

//Check for duplicate email
$sqle = "SELECT count(*) as aacount from ! where email = ?";


$rowe = $db->getRow( $sqle, array( USER_TABLE, $email ) );

$err =0;

if (substr_count($_SESSION['username'],' ') > 0) {
	$err = INVALID_USERNAME;
}

if (substr_count($password,' ') > 0) {
	$err = INVALID_PASSWORD;
}

if ( !preg_match('/^[a-zA-Z0-9\-_]+$/', $_SESSION['username']) ) {
	$err = INVALID_USERNAME;
}
if ($password != $password2 or substr_count($password,' ') > 0) {
	$err = INVALID_PASSWORD;
}

if ( $rowc['aacount'] > 0  or $rowd['aacount'] > 0  ) {

	$err = USERNAME_EXISTS;

} elseif ( $rowe['aacount'] > 0 ) {

	$err = EMAIL_EXISTS;

} elseif ( ! checkdate( $birthmonth, $birthday, $birthyear ) ) {

	$err = INVALID_BIRTHDATE;

} elseif ( $firstname == '' ) {

	$err = FIRSTNAME_REQUIRED;

} elseif ( $lastname == '' ) {

	$err = LASTNAME_REQUIRED;

} elseif ( $email == '' ) {

	$err = EMAIL_REQUIRED;

} elseif ( strlen( $firstname ) > 50 ) {

	$err = FIRSTNAME_LENGTH;

} elseif ( strlen( $lastname ) > 50 ) {

	$err = LASTNAME_LENGTH;

} elseif ( strlen( $email ) > 255 ) {

	$err = EMAIL_LENGTH;

} elseif ( strpos( $firstname, '@' ) > 0 ) {

	$err = FIRSTNAME_REQUIRED;

} elseif ( strpos( $lastname, '@' ) > 0 ) {

	$err = LASTNAME_REQUIRED;

} elseif ( preg_replace( "/[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}/i", "", $email ) != "" ) {

	$err = EMAIL_REQUIRED;

} elseif (($config['accept_about_me'] == 'Y' or $config['accept_about_me'] =='1') &&  $config['about_me_mandatory'] == 'Y' && $about_me == '') {

	$err = ABOUT_ME_MANDATORY;

} elseif ($config['accept_country'] == 'Y' or $config['accept_country'] == '1') {

	if ($config['accept_state'] == 'Y' or $config['accept_state'] == "1") {

		if ( $stateprovince == '' && $config['state_mandatory'] == 'Y' ) {

			$err = STATEPROVINCE_NEEDED;

		} elseif ( $county == ''  && $config['county_mandatory'] == 'Y' && ($config['accept_county'] == 'Y' or $config['accept_county'] == "1")) {

			$err = COUNTY_REQUIRED;

		} elseif ($config['accept_city'] == 'Y' or $config['accept_city'] == "1") {

			if ($city == ''  && $config['city_mandatory'] == 'Y') {

				$err = CITY_REQUIRED;

			} elseif ( strlen( $city ) > 255 ) {

				$err = CITY_LENGTH;
			}

		} elseif ( $zip == ''  && $config['zipcode_mandatory'] == 'Y' && ($config['accept_zipcode'] == 'Y' or $config['accept_zipcode'] == "1")) {

			$err = ZIP_REQUIRED;
		}
	}

} elseif ( $lookageend < $lookagestart && ($config['accept_lookage'] == 'Y' or $config['accept_lookage'] == "1") ) {

	$err = BIGGER_STARTAGE;

} elseif ($timezone == '-25' && $config['timezone_mandatory'] == 'Y' && ($config['accept_timezone'] == 'Y' or $config['accept_timezone'] == "1" ) ) {

	$err = INVALID_TIMEZONE;

}
if (strtolower($_POST['spam_code']) != strtolower($_SESSION['spam_code']) || !isset($_SESSION['spam_code']) || $_SESSION['spam_code'] == NULL ) {

	$err = INVALID_SPAMCODE;

}

if ($gender == 'C' ) {
	if (trim($couple_usernames) == '' or substr_count($couple_usernames,',') <= 0 or !isset($couple_usernames) ) {
		$err = COUPLE_USERNAMES_MISSING;
	} else {
		$userok = 0;
		$usrs = 0;
		foreach(explode(',',$couple_usernames) as $k => $uname) {
			if (trim($uname) != '') {
				$user = $db->getOne('select username from ! where username = ?', array(USER_TABLE, trim($uname)) );
				$usrs++;
				if ($user != trim($uname)) {$userok++;}
			}
		}
		if ($userok > 0 ) {$err = 129; }
		if ($usrs < 2) {$err = COUPLE_USERNAMES_MISSING;}
	}
}

if (  $err > 0 ) {

	header ( "location: signup.php?errid=$err" );
	exit();
}

$active =  0;

$lastvisit = $regdate = time();

$level = ($config['default_user_level']!='')? $config['default_user_level']:4;

$activedays = $db->getOne('select activedays from ! where roleid = ?', array( MEMBERSHIP_TABLE, $level ) );

$levelend = time();

$status = 'approval';

//$levelend = strtotime("+$activedays day",time());
if ($config['default_active_status'] == 'Y') {

	$levelend = strtotime("+$activedays day",time());

	// $status = get_lang('status_enum','active');
	$status = 'active';
}
if ($config['bypass_regconfirm'] == 'Y') {

	$active=1;

	$actkey = 'Confirmed';

	$conf="1";

} else {

	$actkey = md5( $email . time() );
}

$rank = 1;


// $status =  get_lang('status_enum','approval') ;

$pwd = md5( $password );

$sqlins = "INSERT INTO !
				(
				active,
				username,
				password,
				lastvisit,
				regdate,
				level,
				timezone,
				allow_viewonline,
				rank,
				email,
				country,
				actkey,
				firstname,
				lastname,
				gender,
				lookgender,
				lookagestart,
				lookageend,
				lookcountry,
				address_line1,
				address_line2,
				state_province,
				county,
				city,
				zip,
				lookstate_province,
				lookcounty,
				lookcity,
				lookzip,
				lookradius,
				radiustype,
				birth_date,
				status,
				about_me,
				couple_usernames,
				levelend)
		 VALUES (  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

//Insert record
//Bernd > removed the date function in SQL statement.
$result = $db->query ( $sqlins, array( USER_TABLE, $active, $username, $pwd, $lastvisit, $regdate, $level, $timezone, $viewonline, $rank, $email, $from, $actkey, $firstname, $lastname, $gender, $lookgender, $lookagestart, $lookageend, $lookfrom, $address1, $address2, $stateprovince, $county, $city, $zip, $lookstateprovince, $lookcounty, $lookcity, $lookzip, $lookradius, $radiustype, $birthdate, $status,  $about_me, $couple_usernames, $levelend ) );

$lastid = $db->getOne('select id from ! where username = ?', array(USER_TABLE, $username));

//Store the id in session
$_SESSION['TempUserId'] = $lastid;

/* $user_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : ( ( !empty($_ENV['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : getenv('REMOTE_ADDR') );
*/

/*
//Create user in Forum when the user tries to login to the forum
if ($config['forum_installed'] != '' && $config['forum_installed'] != 'None'
) {
	forum_savesignup($username, $password, $email);
}
*/
//update referals
if ( $_SESSION['ReferalId'] ) {

	$sql = "INSERT INTO ! (  affid, userid ) VALUES (  ?, ? )";

	$db->query( $sql, array( AFFILIATE_REFERALS_TABLE, $_SESSION['ReferalId'], $lastid ));

}

/* Create dummy activation key */
if ($config['bypass_regconfigm'] == 'Y') {
	$actkey = md5( $email . time() );
}

$Subject = get_lang('profile_confirmation_email_sub');

$From = $config['admin_email'];

$To = $firstname.' '.$lastname.'<'.$email.'>';

$body = get_lang('profile_confirmation_email', MAIL_FORMAT);

$body = str_replace( '#FirstName#',  $firstname , $body );

$body = str_replace( '#ConfCode#',  $actkey , $body );

$body = str_replace('#Welcome#', get_lang('welcome'), $body);

$body = str_replace( '#ConfirmationLink#',  HTTP_METHOD . $_SERVER['SERVER_NAME'] . DOC_ROOT . 'completereg.php?confcode' , $body );

$body = str_replace( '#StrID#',  $username , $body );

$body = str_replace( '#Email#',  $email , $body );

$body = str_replace( '#Password#',  $password , $body );

$body = str_replace( '#Upgrade#',  get_lang('upgrade_membership') , $body );

mailSender($From, $To, $email, $Subject, $body);

if ($config['newuser_admin_info'] == 'Y') {
	/* Now send email to admin about this new user signup */

	$body = get_lang('newuser', MAIL_FORMAT);

	$body = str_replace( '#UserName#',  $username , $body );

	$Subject = get_lang('newuser_sub'). ' - ' . $config['site_name'];

	$From = $config['admin_email'];

	$To = $config['admin_email'];

	$email = $config['admin_email'];

	mailSender($From, $To, $email, $Subject, $body);

}

header( 'location: confirmreg.php?conf='.$conf );
?>
