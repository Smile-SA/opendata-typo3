<?php
$cwd = getcwd();
chdir(dirname(__FILE__));
$content = `php searchdatasets.php`;
chdir($cwd);

