<?php

require_once(dirname(__FILE__).'/init.php');

if (!isset($_GET['a']) || empty($_GET['a']) || !isset($_GET['v']) || empty($_GET['v'])) { $db->disconnect(); return ''; }

if (trim($_GET['a']) == 'country') {
	$cntry = $_GET['v'];
} else {
	$cntry = $_GET['v1'];
}

if ($cntry == 'US') $_SESSION['radiustype'] = 'miles';

$zipsAvailable = zipsAvailable($cntry);

$zipsDisp ='<table width="100%" border=0 cellspacing=0 cellpadding=0><tr><td width="160">'.
		get_lang('search_within').
		':&nbsp;</td><td><input name="srchradius" value="'.$_SESSION['srchradius'].
		'" type=text size="5" maxlength="10" />&nbsp;'.
		'<input type=radio name="radiustype" value="miles"';
$zipsDisp .= ($_SESSION['radiustype'] == 'miles')? 'checked':'';
$zipsDisp .= ' />'.get_lang('miles').'<input type=radio name="radiustype" value="kms" ';
$zipsDisp .= ($_SESSION['radiustype'] == 'kms')?' checked ':'';
$zipsDisp .= ' />'.get_lang('kms'). get_lang('of_zip_code').'</td></tr></table>';

switch (trim($_GET['a'])) {

	case 'country':
		$rtn= '|||srchlookstate_province|:|' . stateOptions($pre)
			. '|||srchlookcounty|:|' . '<input name="srchlookcounty" type="text" size="30" maxlength="100" />'
			. '|||srchlookcity|:|' . '<input name="srchlookcity" type="text" size="30" maxlength="100" />'
			. '|||srchlookzip|:|' . '<input name="srchlookzip" type="text" size="10" maxlength="100" />';
		if ($zipsAvailable == 1) {
			$rtn.='|||zipsavailable|:|' .$zipsDisp;
		} else {
			$rtn.='|||zipsavailable|:|' .'<td></td>';
		}
		print $rtn;
		break;

	case 'state':
		$rtn= '|||srchlookcounty|:|' . countyOptions($pre)
			. '|||srchlookcity|:|' . '<input name="srchlookcity" type="text" size="30" maxlength="100" />'
			. '|||srchlookzip|:|' . '<input name="srchlookzip" type="text" size="10" maxlength="100" />';
		if ($zipsAvailable == 1) {
			$rtn.='|||zipsavailable|:|' .$zipsDisp;
		} else {
			$rtn.='|||zipsavailable|:|' .'<td></td>';
		}
		print $rtn;
		break;

	case 'county':
		$rtn= '|||srchlookcity|:|' . cityOptions($pre)
			. '|||srchlookzip|:|' . '<input name="srchlookzip" type="text" size="10" maxlength="100" />';
		if ($zipsAvailable == 1) {
			$rtn.='|||zipsavailable|:|' .$zipsDisp;
		} else {
			$rtn.='|||zipsavailable|:|' .'<td></td>';
		}
		print $rtn;
		break;

	case 'city':
		$rtn= '|||srchlookzip|:|' . zipOptions($pre);
		if ($zipsAvailable == 1) {
			$rtn.='|||zipsavailable|:|' .$zipsDisp;
		} else {
			$rtn.='|||zipsavailable|:|' .'<td></td>';
		}
		print $rtn;
		break;

	default : print ''; break;
}

function stateOptions() { //echo 'yeah';
	$data = getStates(trim($_GET['v']),'N');
//var_dump($data);
	if (count($data) < 1) return '<input name="srchlookstate_province" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select"  name="srchlookstate_province" onchange="javascript:  cascadeState(this.value,this.form.srchlookcountry.value);" >';

	$ret .= '<option value="-1">'.get_lang('select_state').'</option>';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

function countyOptions() {
	$data = getCounties(trim($_GET['v1']),trim($_GET['v']),'N');

	if (count($data) < 1) return '<input name="srchlookcounty" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" name="srchlookcounty" onchange="javascript:  cascadeCounty(this.value,this.form.srchlookcountry.value,this.form.srchlookstate_province.value);" >';

	$ret .= '<option value="-1">'.get_lang('select_county').'</option>';
	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

function cityOptions() {
	$data = getCities(trim($_GET['v1']),trim($_GET['v2']),trim($_GET['v']),'N');

	if (count($data) < 1) return '<input name="srchlookcity" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" name="srchlookcity" onchange="javascript: cascadeCity(this.value,this.form.srchlookcountry.value,this.form.srchlookstate_province.value,this.form.srchlookcounty.value);" >';

	$ret .= '<option value="-1">'.get_lang('select_city').'</option>';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}


function zipOptions() {
	$data = getZipcodes(trim($_GET['v1']),trim($_GET['v2']),trim($_GET['v3']),trim($_GET['v']),'N');

	if (count($data) < 1) return '<input name="srchlookzip" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select"  name="srchlookzip" >';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

$db->disconnect();
?>
