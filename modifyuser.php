<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );
}
include( 'sessioninc.php' );

$userid = $_SESSION['UserId'];

$modified['username'] = $username = addslashes(stripEmails(strip_tags($_POST['txtusername'] )));

$modified['firstname'] = $firstname = addslashes(stripEmails(strip_tags(trim($_POST[ 'txtfirstname' ]))));

$modified['lastname'] = $lastname = addslashes(stripEmails(strip_tags(trim($_POST[ 'txtlastname' ]))));

$modified['about_me'] = $about_me = addslashes(stripEmails(strip_tags(trim($_POST[ 'about_me' ]))));

$modified['email'] = $email = addslashes(strip_tags(trim($_POST[ 'txtemail' ])));

$modified['gender'] = $gender = $_POST[ 'txtgender' ];

$modified['couple_usernames'] = $couple_usernames = addslashes(strip_tags(trim($_POST[ 'couple_usernames' ])));

$modified['birthmonth'] = $birthmonth = $_POST[ 'txtbirthMonth' ];

$modified['birthday'] = $birthday = $_POST[ 'txtbirthDay' ];

$modified['birthyear'] = $birthyear = $_POST[ 'txtbirthYear' ];

$modified['birth_date'] = strtotime($birthyear.'-'.$birthmonth.'-'.$birthday);

$modified['country'] = $from = strip_tags($_POST[ 'txtfrom' ]);

$modified['county'] = $county = strip_tags($_POST[ 'txtcounty' ]);

$modified['zip'] = $zip = strip_tags(trim($_POST[ 'txtzip' ]));

$modified['timezone'] = $timezone = $_POST['txttimezone'];

$modified['lookgender'] = $lookgender = $_POST[ 'txtlookgender' ];

$modified['lookagestart'] = $lookagestart = $_POST[ 'txtlookagestart' ];

$modified['lookageend'] = $lookageend = $_POST[ 'txtlookageend' ];

$modified['city'] = $city = trim($_POST[ 'txtcity' ]);

$modified['state_province'] = $stateprovince = addslashes(strip_tags(trim($_POST[ 'txtstateprovince' ])));

$modified['address1'] = $address1 = addslashes(stripEmails(strip_tags(trim($_POST['txtaddress1' ]))));

$modified['address2'] = $address2 = addslashes(stripEmails(strip_tags(trim($_POST['txtaddress2' ]))));

$modified['lookcountry'] = $lookfrom = $_POST[ 'txtlookfrom' ];

$modified['lookcounty'] = $lookcounty = addslashes(strip_tags($_POST[ 'txtlookcounty' ]));

$modified['lookcity'] = $lookcity = addslashes(strip_tags(trim($_POST[ 'txtlookcity' ])));

$modified['lookstate_province'] = $lookstateprovince = addslashes(strip_tags(trim($_POST[ 'txtlookstateprovince' ])));

$modified['lookzip'] = $lookzip = addslashes(strip_tags(trim($_POST[ 'txtlookzip' ])));

$modified['allow_viewonline'] = $viewonline = $_POST[ 'txtviewonline' ];

$modified['lookradius'] = $lookradius = addslashes(strip_tags(trim($_POST[ 'lookradius' ])));

$modified['radiustype'] = $radiustype = trim($_POST[ 'radiustype' ]);

/*
if ($_POST['chgcntry'] == '1') {
	$_SESSION['lookstateprovince'] = '';
	$_SESSION['modifiedrow'] = $modified;
	header ( "location: edituser.php" );
	exit();
}
*/

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

if ( $firstname == '' ) {

	$err = FIRSTNAME_REQUIRED;

} elseif ( $lastname == '' ) {

	$err = LASTNAME_REQUIRED;

} elseif ( $email == '' ) {

	$err = EMAIL_REQUIRED;

} elseif ( strlen( $firstname ) > 50 ) {

	$err = FIRSTNAME_LENGTH;

} elseif ( strlen( $lastname ) > 25 ) {

	$err = LASTNAME_LENGTH;

} elseif ( strlen( $email ) > 255 ) {

	$err = EMAIL_LENGTH;

} elseif ( strpos( $firstname, '@' ) > 0 ) {

	$err = FIRSTNAME_REQUIRED;

} elseif ( strpos( $lastname, '@' ) > 0 ) {

	$err = LASTNAME_REQUIRED;

} elseif ( preg_replace( "/[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}/i", "", $email ) != "" ) {

	$err = EMAIL_REQUIRED;

} elseif ( ! checkdate( $birthmonth, $birthday, $birthyear ) ) {

	$err = INVALID_BIRTHDATE;

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

if ($gender == 'C' ) {
	if (trim($couple_usernames) == '' or substr_count($couple_usernames,',') <= 0 or !isset($couple_usernames) ) {
		$err = COUPLE_USERNAMES_MISSING;
	} else {
		$userok = 0;
		$usrs=0;
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


$_SESSION['modifiedrow'] = $modified;

if (  $err != 0 ) {

	header ( "location: edituser.php?errid=$err" );

	exit();

}

//	$birthdate = strtotime ( $birthday . ' ' . $birthmonth . ' ' . $birthyear );
$birthdate = $birthyear . '-' . $birthmonth . '-' . $birthday;

$sqlins = "UPDATE ! SET
					allow_viewonline 	= ?,
					email 				= ?,
					country 			= ?,
					firstname 			= ?,
					lastname 			= ?,
					gender 				= ?,
					lookgender 			= ?,
					lookagestart 		= ?,
					lookageend 			= ?,
					lookcountry 		= ?,
					address_line1 		= ?,
					address_line2 		= ?,
					state_province 		= ?,
					county 				= ?,
					city 				= ?,
					zip 				= ?,
					timezone 			= ?,
					lookzip				= ?,
					lookcity 			= ?,
					lookcounty 			= ?,
					lookstate_province 	= ?,
					lookradius			= ?,
					about_me			= ?,
					radiustype			= ?,
					couple_usernames	= ?,
					birth_date = ?
					WHERE id=?";


$result = $db->query( $sqlins, array( USER_TABLE, $viewonline, $email, $from, $firstname, $lastname,  $gender, $lookgender, $lookagestart, $lookageend, $lookfrom, $address1, $address2, $stateprovince, $county, $city, $zip, $timezone, $lookzip, $lookcity, $lookcounty, $lookstateprovince, $lookradius, $about_me, $radiustype, $couple_usernames, $birthdate, $userid ) );

$sql = 'SELECT id FROM ! WHERE enabled = ? ORDER BY
	displayorder ASC LIMIT 0, 1';

$_SESSION['modifiedrow'] = '';

$ow = $db->getRow( $sql, array( SECTIONS_TABLE, 'Y' ) );

$_SESSION['FullName'] = $firstname . ' ' . $lastname;

$nextsectionid = $db->getOne('select id from ! where enabled = ? order by displayorder asc limit 1',array(SECTIONS_TABLE, 'Y') );

header( 'location: editquestions.php?sectionid='.$nextsectionid );

?>