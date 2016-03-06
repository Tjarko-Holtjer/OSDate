<?php

$sql = 'SELECT * FROM ! where enabled = ? order by date desc limit 0,1';

$temp = $db->getAll( $sql, array( STORIES_TABLE, 'Y' ) );

$index = 0;

$data = array();

foreach( $temp as $index => $row ) {

	$sql = 'SELECT * FROM ! where id= ?';

	$row1 = $db->getRow( $sql, array( USER_TABLE, $row[sender] ) );

	$row[ 'username' ] = $row1['username'];

	$arrtext = explode( ' ', $row['text'], $config['length_story'] + 1);

	$arrtext[$config['length_story']] = '';

	$row['text'] = trim(implode( ' ', $arrtext)) . '...';

	$row['date'] = date(get_lang('DISPLAY_DATE_FORMAT'), $row['date'] );

	$data[ $index ] = $row;

	$index++;
}


$t->assign ( 'story_data', $data );


?>