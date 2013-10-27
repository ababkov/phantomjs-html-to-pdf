phantomjs-html-to-pdf
=====================

PHP library that provides an easy interface to phantomjs HTML to PDF / rasterize capability.

Good alternative to wkhtmltopdf.

This library does depend on the phantom js being installed on your system. Binaries can be found here: http://phantomjs.org/download.html

## Basic Usage
```$renderer = new Rex\PhantomJs\Renderer();
$renderer->setHtmlContentFromUri("http://www.google.com");
$output_file = $renderer->save();```

## Custom Binary Path
If the phantomjs binary is not in your system path you should call setBinaryPath
```$renderer = new Rex\PhantomJs\Renderer();
$renderer->setHtmlContentFromUri("C:\\Program Files (x86)\\PhantomJs\\phantomjs.exe");```

## Setting Options
```$renderer = new Rex\PhantomJs\Renderer();
$renderer->setHtmlContentFromUri("http://www.google.com");
$renderer->setOption(Constants::OPTION_MARGIN,"1cm");
$output_file = $renderer->save();```

## Available Options
'format' => The page format e.g. 'A4', '10cm*20cm' or any of the Constants::FORMAT_* constants
'margin_left' => The left margin as an int / float + a unit. E.g. 1cm or 1.1in
'margin_right' => The right margin as an int / float + a unit. E.g. 1cm or 1.1in
'margin_top' => The top margin as an int / float + a unit. E.g. 1cm or 1.1in
'margin_bottom' => The bottom margin as an int / float + a unit. E.g. 1cm or 1.1in
'orientation' => The orientation: Constants::ORIENTATION_PORTRAIT, Constants::ORIENTATION_LANDSCAPE
'zoom' => The zoom level where 1 is 100%. e.g. for 140% use 1.4
'header_html' => Html to be used in the header. Use {{page_number}} for the page number, {{total_pages}} for the total pages. Ensure you also set the header height option.
'footer_html' => Html to be used in the footer. Use {{page_number}} for the page number, {{total_pages}} for the total pages. Ensure you also set the footer height option.
'header_height' => The height of the footer as an int / float + a unit. E.g. 1cm or 1.1in
'footer_height' => The height of the header as an int / float + a unit. E.g. 1cm or 1.1in
'wait_time' => The wait time in ms

## Serve for Download
```use Rex\PhantomJs\Constants;

$renderer = new Rex\PhantomJs\Renderer();
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
```

## Examples
If the binary isn't in your path, make sure you set it in examples/config.php.