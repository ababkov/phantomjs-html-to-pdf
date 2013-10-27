<?php

require("config.php");

use Rex\PhantomJs\Constants;

$renderer = new Rex\PhantomJs\Renderer();
if( $CONFIG['bin_path'] )
	$renderer->setBinPath($CONFIG['bin_path']);
$renderer->setHtmlContentFromUri("http://www.google.com");
$renderer->setOption(Constants::OPTION_MARGIN,"1cm");
$output_file = $renderer->save();

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($output_file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($output_file));
ob_clean();
flush();
readfile($output_file);
exit();