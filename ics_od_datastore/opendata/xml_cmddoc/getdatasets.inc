<?php
$cwd = getcwd();
chdir(dirname(__FILE__));
$content = `php getdatasets.php`;
chdir($cwd);

