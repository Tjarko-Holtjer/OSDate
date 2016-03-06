<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );

// If a plugin is provided, time to process and display it's panel
//


if ( isset($_REQUEST['plugin']) ) {

    $param['plugin'] = $_REQUEST['plugin'];

    $mod->modDisplayPluginPage($param);
    if ($param['plugin'] == "googleMap") {
        $t->assign('google_map', 'Y');
    }	
}

$t->assign('lang',$lang);
$t->display( 'index.tpl' );

exit;

?>