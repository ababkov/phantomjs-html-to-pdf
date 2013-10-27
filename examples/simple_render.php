<?php

require("config.php");

$renderer = new Rex\PhantomJs\Renderer();
if( $CONFIG['bin_path'] )
	$renderer->setBinPath($CONFIG['bin_path']);
$renderer->setHtmlContentFromUri("http://www.google.com");
$output_file = $renderer->save();

echo $output_file;