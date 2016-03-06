<?PHP
$callscript=$_SERVER['SCRIPT_NAME'];

$calldir=str_replace('admin/','',substr($callscript,0,strrpos($callscript,'/')+1));

define('DOC_ROOT', $calldir);

define( 'VERSION', '2.0.7' );

?>