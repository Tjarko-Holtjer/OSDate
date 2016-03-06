<?php
require_once(dirname(__FILE__).'/init.php');

if (!isset($_GET['a']) || empty($_GET['a']) || !isset($_GET['v']) || empty($_GET['v'])) { 	$db->disconnect(); return '';}
if (trim($_GET['a']) == 'country') {
	$cntry = $_GET['v'];
} else {
	$cntry = $_GET['v1'];
}
if ($cntry == 'US') $_SESSION['radiustype'] = 'miles';

$zipsAvailable = zipsAvailable($cntry);
$zipsDisp = '<table width="100%" cellpadding=0 border=0 cellspacing=0>'.
	'<tr><td width="188" valign="middle">'.get_lang('search_within').'</td><td valign="middle" width="15">'.
	'<input name="lookradius" value="'.$_SESSION['lookradius'].'" type=text size="5" maxlength="10" /></td>'.
	'<td valign="middle" width="6">'.
	'<input type=radio name="radiustype" value="miles"';
$zipsDisp .= ($_SESSION['radiustype'] == 'miles')? 'checked':'';
$zipsDisp .= '/></td><td width="15" valign="middle">'.get_lang('miles').
	'</td><td width="6" valign="middle"><input type=radio name="radiustype" value="kms"';
$zipsDisp .= ($_SESSION['radiustype'] == 'kms')?' checked ':'';
$zipsDisp .='/></td><td valign="middle" width="20">'.get_lang('kms').'</td><td  valign="middle">&nbsp;'.get_lang('of_zip_code').'</td></tr></table>';

switch (trim($_GET['a'])) {

	case 'country':
		echo '|||txtlookstateprovince|:|' . stateOptions($pre);
		if ($config['accept_lookcounty'] == 'Y' or $config['accept_lookcounty'] == '1') {
			echo '|||txtlookcounty|:|' . '<input name="txtlookcounty" type="text" size="30" maxlength="100" />';
		}
		if ($config['accept_lookcity'] == 'Y' or $config['accept_lookcity'] == '1') {
			echo '|||txtlookcity|:|' . '<input name="txtlookcity" type="text" size="30" maxlength="100" />';
		}
		if ($config['accept_lookzip'] == 'Y' or $config['accept_lookzip'] == '1') {
			echo '|||txtlookzip|:|' . '<input name="txtlookzip" type="text" size="30" maxlength="100" />';
		}
		if ($zipsAvailable == 1) {
			echo '|||zipsavailable|:|' .$zipsDisp;
		} else {
			echo '|||zipsavailable|:|' .' ';
		}
		break;

	case 'state':
		echo '|||txtlookcounty|:|' . countyOptions($pre);
		if ($config['accept_lookcity'] == 'Y' or $config['accept_lookcity'] == '1') {
			echo '|||txtlookcity|:|' . '<input name="txtlookcity" type="text" size="30" maxlength="100" />';
		}
		if ($config['accept_lookzip'] == 'Y' or $config['accept_lookzip'] == '1') {
			echo '|||txtlookzip|:|' . '<input name="txtlookzip" type="text" size="30" maxlength="100" />';
		}
		if ($zipsAvailable == 1) {
			echo('|||zipsavailable|:|' .$zipsDisp);
		}else {
			echo('|||zipsavailable|:|' .' ');
		}
		break;

	case 'county':
		echo '|||txtlookcity|:|' . cityOptions($pre);
		if ($config['accept_lookzip'] == 'Y' or $config['accept_lookzip'] == '1') {
			echo '|||txtlookzip|:|' . '<input name="txtlookzip" type="text" size="30" maxlength="100" />';
		}
		if ($zipsAvailable == 1) {
			echo('|||zipsavailable|:|' .$zipsDisp);
		}else {
			echo('|||zipsavailable|:|' .' ');
		}
		break;

	case 'city':
		echo '|||txtlookzip|:|' . zipOptions($pre);
		if ($zipsAvailable == 1) {
			echo('|||zipsavailable|:|' .$zipsDisp);
		}else {
			echo('|||zipsavailable|:|' .' ');
		}
		break;

	default : return ''; break;
}

function stateOptions() { //echo 'yeah';
	$data = getStates(trim($_GET['v']),'Y');
//var_dump($data);
	if (count($data) < 1) return '<input name="txtlookstateprovince" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtlookstateprovince" onchange="javascript: this.form.chgcntry.value=\'1\'; cascadeStateL(this.value,this.form.txtlookfrom.value,\'txtlookcounty\');" >';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

function countyOptions() {
	$data = getCounties(trim($_GET['v1']),trim($_GET['v']),'Y');

	if (count($data) < 1) return '<input name="txtlookcounty" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtlookcounty" onchange="javascript: this.form.chgcntry.value=\'1\'; cascadeCountyL(this.value,this.form.txtlookfrom.value,this.form.txtlookstateprovince.value,\'txtlookcity\');" >';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

function cityOptions() {
	$data = getCities(trim($_GET['v1']),trim($_GET['v2']),trim($_GET['v']),'Y');

	if (count($data) < 1) return '<input name="txtlookcity" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtlookcity" onchange="javascript: this.form.chgcntry.value=\'1\'; cascadeCityL(this.value,this.form.txtlookfrom.value,this.form.txtlookstateprovince.value,this.form.txtlookcounty.value,\'txtlookzip\');" >';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}


function zipOptions() {
	$data = getZipcodes(trim($_GET['v1']),trim($_GET['v2']),trim($_GET['v3']),trim($_GET['v']),'Y');

	if (count($data) < 1) return '<input name="txtlookzip" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtlookzip" >';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

$db->disconnect();

?>
