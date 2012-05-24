<?php
$cwd = getcwd();
chdir(dirname(__FILE__));
$content = `php getlicences.php`;
chdir($cwd);

