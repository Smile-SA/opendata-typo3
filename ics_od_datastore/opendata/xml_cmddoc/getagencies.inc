<?php
$cwd = getcwd();
chdir(dirname(__FILE__));
$content = `php getagencies.php`;
chdir($cwd);

