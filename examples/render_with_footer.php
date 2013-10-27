<?php

require("config.php");

use Rex\PhantomJs\Constants;

set_time_limit(15);
$renderer = new Rex\PhantomJs\Renderer();
if( $CONFIG['bin_path'] )
	$renderer->setBinPath($CONFIG['bin_path']);
$renderer->setHtmlContentFromUri("http://www.yahoo.com");
$renderer->setOption(Constants::OPTION_HEADER_HTML,"<style type='text/css'>h1{font-size:10px;font-family:Helvetica, Arial, sans-serif}</style><h1>Header <span style='float:right'>{{page_number}} / {{total_pages}}</span></h1>");
$renderer->setOption(Constants::OPTION_HEADER_HEIGHT,"30px");
$renderer->setOption(Constants::OPTION_FOOTER_HTML,"<style type='text/css'>h1{font-size:10px;font-family:Helvetica, Arial, sans-serif}</style><h1>Footer <span style='float:right'>{{page_number}} / {{total_pages}}</span></h1>");
$renderer->setOption(Constants::OPTION_FOOTER_HEIGHT,"30px");
$output_file = $renderer->save();

echo $output_file;