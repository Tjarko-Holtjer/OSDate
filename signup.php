<?php

if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );

}


$_SESSION['from'] = $countrycode = ($_SESSION['from']!= '') ? $_SESSION['from'] : $config['default_country'];

$lang['states'] = getStates($countrycode,'N');

if (count($lang['states']) == 1) {
	foreach ($lang['states'] as $key => $val) {
		$_SESSION['stateprovince'] = $key;
	}
}

if ($_SESSION['stateprovince'] != '') {

	$lang['counties'] = getCounties($countrycode, $_SESSION['stateprovince'], 'N');

	if (count($lang['counties']) == 1) {
		foreach ($lang['counties'] as $key => $val) {
			$_SESSION['countycode'] = $key;
		}
	}

	if ($_SESSION['countycode'] != '') {

		$lang['cities'] = getCities($countrycode, $_SESSION['stateprovince'], $_SESSION['countycode'], 'N');

		if (count($lang['cities']) == 1) {
			foreach($lang['cities'] as $key => $val) {
				$_SESSION['citycode'] = $key;
			}
		}

		if ($_SESSION['citycode'] != '') {

			$lang['zipcodes'] = getZipcodes($countrycode, $_SESSION['stateprovince'], $_SESSION['countycode'], $_SESSION['citycode'], 'N');
		}
	}
}

$_SESSION['lookfrom'] = $lookcountrycode = ($_SESSION['lookfrom']!= '') ?

$_SESSION['lookfrom'] : $config['default_country'];
/*
$_SESSION['timezone'] = '-25';
*/
$lang['lookcountries'] = $allcountries;

$lang['lookstates'] = getStates($lookcountrycode,'Y');

$lang['signup_gender_values'] = get_lang_values('signup_gender_values');

$lang['signup_gender_look'] = get_lang_values('signup_gender_look');

$lang['tz'] = get_lang_values('tz');

$zipsavailable = $db->getOne('select count(*) from ! where countrycode=?', array(ZIPCODES_TABLE, $lookcountrycode) );

$t->assign('zipsavailable', $zipsavailable);

if ($_SESSION['lookstateprovince'] != '') {

	$lang['lookcounties'] = getCounties($lookcountrycode, $_SESSION['lookstateprovince'], 'Y');

	if (count($lang['lookcounties']) == 1) {
		foreach ($lang['lookcounties'] as $key => $val) {
			$_SESSION['lookcounty'] = $key;
		}
	}

	if ($_SESSION['lookcounty'] != '') {

		$lang['lookcities'] = getCities($lookcountrycode, $_SESSION['lookstateprovince'], $_SESSION['lookcounty'], 'Y');

		if (count($lang['lookcities']) == 1) {
			foreach($lang['lookcities'] as $key => $val) {
				$_SESSION['lookcity'] = $key;
			}
		}

		if ($_SESSION['lookcity'] != '') {

			$lang['lookzipcodes'] = getZipcodes($lookcountrycode, $_SESSION['lookstateprovince'], $_SESSION['lookcounty'], $_SESSION['lookcity'], 'Y');
		}
	}
}

$_SESSION['radiustype'] = 'kms';

if ($lookcountrycode == 'US') {$_SESSION['radiustype'] = 'miles';}

$t->assign('password',$_SESSION['password'] );

$t->assign('password2',$_SESSION['password2'] );

$_SESSION['txtlookagestart'] = ($_SESSION['txtlookagestart'] > 0)? $_SESSION['txtlookagestart']:($config['end_year']*-1);

$_SESSION['txtlookageend'] = ($_SESSION['txtlookageend'] > 0)? $_SESSION['txtlookageend']:($config['start_year']*-1);

$t->assign ( 'signup_error',get_lang('errormsgs',$_GET['errid']) );

if ($_SESSION['selectedtime'] != '') {

	$t->assign('selectedtime', $_SESSION['selectedtime']);
	$_SESSION['selectedtime'] = '';

} else {

	$t->assign( 'selectedtime' , date( 'Y-m-d', mktime( 0, 0, 0, date('m'), date('d'), date('y')-25)) );
}
$t->assign('lang', $lang);

$t->assign('rendered_page',$t->fetch('signup.tpl'));

$t->display( 'index.tpl' );

?>
