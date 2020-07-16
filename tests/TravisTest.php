<?php

$server = proc_open(PHP_BINARY . " src/pocketmine/PocketMine.php --no-wizard --disable-readline", [
	0 => ["pipe", "r"],
	1 => ["pipe", "w"],
	2 => ["pipe", "w"]
], $pipes);

if (!is_resource($server)) {
	die('Failed to create process');
}

fwrite($pipes[0], "version\nmakeserver\nstop\n\n");
fclose($pipes[0]);

echo "\n\nReturn value: ". proc_close($server) ."\n";

if (count(glob("plugins/DevTools/Steadfast5*.phar")) === 0) {
	echo "No server PHAR created!\n";
	exit(1);
} else {
	exit(0);
}
