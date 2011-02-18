<?php
$cwd = getcwd();
set_include_path($cwd.'/../' . DIRECTORY_SEPARATOR . get_include_path());
require 'lib/prggmr.php';
\prggmr::initalize();