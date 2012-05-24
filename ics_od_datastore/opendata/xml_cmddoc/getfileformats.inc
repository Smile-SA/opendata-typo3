<?php
$cwd = getcwd();
chdir(dirname(__FILE__));
$content = `php getfileformats.php`;
chdir($cwd);

