<?php

require_once(dirname(__FILE__).'/init.php');


if (!isset($_GET['a']) || empty($_GET['a']) || !isset($_GET['v']) || empty($_GET['v'])) {
	echo '|||txtstateprovince|:|' .'<input name="txtstateprovince" type="text" size="30" maxlength="100" />'
	. '|||txtcounty|:|' . '<input name="txtcounty" type="text" size="30" maxlength="100" />'
		. '|||txtcity|:|' . '<input name="txtcity" type="text" size="30" maxlength="100" />'
		. '|||txtzip|:|' . '<input name="txtzip" type="text" size="30" maxlength="100" />';
	$db->disconnect();
	exit;
}

switch (trim($_GET['a'])) {

	case 'country':
		echo '|||txtstateprovince|:|' . stateOptions()
			. '|||txtcounty|:|' . '<input name="txtcounty" type="text" size="30" maxlength="100" />'
			. '|||txtcity|:|' . '<input name="txtcity" type="text" size="30" maxlength="100" />'
			. '|||txtzip|:|' . '<input name="txtzip" type="text" size="30" maxlength="100" />';
		break;

	case 'state':
		echo '|||'.
			'txtcounty|:|' . countyOptions()
			. '|||txtcity|:|' . '<input name="txtcity" type="text" size="30" maxlength="100" />'
			. '|||txtzip|:|' . '<input name="txtzip" type="text" size="30" maxlength="100" />';
		break;

	case 'county':
		echo '|||'.
			 'txtcity|:|' . cityOptions()
			. '|||txtzip|:|' . '<input name="txtzip" type="text" size="30" maxlength="100" />';
		break;

	case 'city':
		echo '|||'.
			 'txtzip|:|' . zipOptions();
		break;

	default : return ''; break;
}

function stateOptions() { //echo 'yeah';
	$data = getStates(trim($_GET['v']),'N');
//var_dump($data);
	if (count($data) < 1) return '<input name="txtstateprovince" type="text" size="30" maxlength="100" />';
	$ret .= '	<select class="select" style="width: 175px" name="txtstateprovince" onchange="javascript: this.form.chgcntry.value=\'1\'; cascadeState(this.value,this.form.txtfrom.value,\'txtcounty\');" >';

	$ret .= '<option value="-1">'.get_lang('select_state').'</option>';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

function countyOptions() {
	$data = getCounties(trim($_GET['v1']),trim($_GET['v']),'N');

	if (count($data) < 1) return '<input name="txtcounty" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtcounty" onchange="javascript: this.form.chgcntry.value=\'1\'; cascadeCounty(this.value,this.form.txtfrom.value,this.form.txtstateprovince.value,\'txtcity\');" >';

	$ret .= '<option value="-1">'.get_lang('select_county').'</option>';
	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}

function cityOptions() {
	$data = getCities(trim($_GET['v1']),trim($_GET['v2']),trim($_GET['v']),'N');

	if (count($data) < 1) return '<input name="txtcity" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtcity" onchange="javascript: this.form.chgcntry.value=\'1\'; cascadeCity(this.value,this.form.txtfrom.value,this.form.txtstateprovince.value,this.form.txtcounty.value,\'txtzip\');" >';

	$ret .= '<option value="-1">'.get_lang('select_city').'</option>';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}


function zipOptions() {
	$data = getZipcodes(trim($_GET['v1']),trim($_GET['v2']),trim($_GET['v3']),trim($_GET['v']),'N');

	if (count($data) < 1) return '<input name="txtzip" type="text" size="30" maxlength="100" />';

	$ret = '	<select class="select" style="width: 175px" name="txtzip" >';

	foreach ($data as $k => $y) $ret .= "<option value='$k'>$y</option>";

	return $ret .= '</select>';
}
$db->disconnect();

?>
