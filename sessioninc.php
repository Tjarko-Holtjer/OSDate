<?php

if(  $_SESSION['UserId'] == '' ) {

	$get_params = serialize($_GET);
	header('location: index.php?page=login&returnto='.$returnto.'&get_params='.$get_params);
	exit();
}
?>