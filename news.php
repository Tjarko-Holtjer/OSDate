<?php

if ( $config['no_news'] != '0' && $config['no_news'] != '' ) {

	$sql = 'SELECT * FROM ! order by date desc limit 0,' . $config['no_news'];


	$data = $db->getAll( $sql, array( NEWS_TABLE ) );

	foreach( $data as $index => $row ) {

		$row['date'] = date(get_lang('DISPLAY_DATE_FORMAT'), $row['date'] );

	// how many characters should be displayed

		$arrtext = explode( ' ', $row['text'], $config['length_news'] + 1);

		$arrtext[$config['length_news']] = '';

		$row['text'] = trim(implode( ' ', $arrtext)) . '...';

		$data[ $index ] = $row;
	}

$t->assign ( 'news_data', $data );
}
?>