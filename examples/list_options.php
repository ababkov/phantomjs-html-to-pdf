<?php

require("config.php");

$renderer = new Rex\PhantomJs\Renderer();
print_r($renderer->describeOptions());
die();
