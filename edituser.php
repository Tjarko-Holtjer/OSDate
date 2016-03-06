<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );

if ($_SESSION['modifiedrow'] != '') {

	$row = $_SESSION['modifiedrow'] ;

	$row['id'] = $_SESSION['UserId'];

	$_SESSION['modifiedrow'] = '';

} else {
	$sql = 'SELECT id,
			username,
			password,
			allow_viewonline,
			email,
			country,
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
			timezone,
			lookstate_province,
			lookcounty,
			lookcity,
			lookzip,
			lookradius,
			radiustype,
			about_me,
			couple_usernames,
			birth_date FROM ! where id = ?';


	$row = $db->getRow( $sql, array( USER_TABLE, $_SESSION['UserId'] ) );
}

if ( isset( $_GET['errid'] ) ) {
	$t->assign ( 'modify_error', get_lang('errormsgs',$_GET['errid']) );
}

$row['firstname'] = stripslashes($row['firstname']);
$row['lastname'] = stripslashes($row['lastname']);
$row['state_province'] = stripslashes($row['state_province']);
$row['county'] = stripslashes($row['county']);
$row['city'] = stripslashes($row['city']);
$row['zip'] = stripslashes($row['zip']);
$row['address_line1'] = stripslashes($row['address_line1']);
$row['address_line2'] = stripslashes($row['address_line2']);
$row['lookstate_province'] = stripslashes($row['lookstate_province']);
$row['lookcounty'] = stripslashes($row['lookcounty']);
$row['lookcity'] = stripslashes($row['lookcity']);
$row['lookzip'] = stripslashes($row['lookzip']);

$_SESSION['lookradius'] = $row['lookradius'];
$_SESSION['radiustype'] = $row['radiustype'];

$_SESSION['from'] = $countrycode = $row['country'];

$lang['states'] = getStates($countrycode,'N');

if ($row['state_province'] != '') {

	$lang['counties'] = getCounties($countrycode, $row['state_province'], 'N');

	if (count($lang['counties']) == 1) {
		foreach ($lang['counties'] as $key => $val) {
			$row['county'] = $key;
		}
	}
}
if ($row['county'] != '') {

	$lang['cities'] = getCities($countrycode, $row['state_province'], $row['county'], 'N');

	if (count($lang['cities']) == 1) {
		foreach($lang['cities'] as $key => $val) {
			$row['city'] = $key;
		}
	}
}
if ($row['city'] != '') {

	$lang['zipcodes'] = getZipcodes($countrycode, $row['state_province'], $row['county'], $row['city'], 'N');
}

$_SESSION['lookfrom'] = $lookcountrycode = $row['lookcountry'];

$lang['lookcountries'] = $allcountries;

$lang['lookstates'] = getStates($lookcountrycode);

$lang['signup_gender_values'] = get_lang_values('signup_gender_values');

$lang['signup_gender_look'] = get_lang_values('signup_gender_look');

$lang['tz'] = get_lang_values('tz');

$zipsavailable = $db->getOne('select count(*) from ! where countrycode=?', array(ZIPCODES_TABLE, $lookcountrycode) );
if (!isset($row['radiustype']) or $row['radiustype'] == '') {
	if ($lookcountrycode == 'US') {
		$row['radiustype'] = 'miles';
	} else {
		$row['radiustype'] = 'kms';
	}
}
$t->assign('zipsavailable', $zipsavailable);

if ($row['lookstate_province'] != '') {

	$lang['lookcounties'] = getCounties($lookcountrycode, $row['lookstate_province'], 'Y');

	if (count($lang['lookcounties']) == 1) {
		foreach ($lang['lookcounties'] as $key => $val) {
			$row['lookcounty'] = $key;
		}
	}

	if ($row['lookcounty'] != '') {

		$lang['lookcities'] = getCities($lookcountrycode, $row['lookstate_province'], $row['lookcounty'], 'Y');

		if (count($lang['lookcities']) == 1) {
			foreach($lang['lookcities'] as $key => $val) {
				$row['lookcity'] = $key;
			}
		}

		if ($row['lookcity'] != '') {

			$lang['lookzipcodes'] = getZipcodes($lookcountrycode, $row['lookstate_province'], $row['lookcounty'], $row['lookcity'], 'Y');
		}
	}
}

$t->assign('lang', $lang);

// fix this later (Windows compatibility with pre-1970 dates)

if ( $row['birth_date'] == -1 && $row['birthyear'] < 1970 ) {

	if ( strlen( $row['birthday'] ) == 1 ) {
		$row['birthday'] = '0' . $row['birthday'];
	}

	$row['birth_date'] = $row['birthyear'] . '-' . $row['birthmonth'] . '-' . $row['birthday'];
}

$t->assign( 'user', $row );

$t->assign('rendered_page', $t->fetch('edituser.tpl') );

$t->display( 'index.tpl' );

exit;

?>