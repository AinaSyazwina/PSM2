<?php
// Assume server is at 127.0.0.1 and port 5000
$host = '127.0.0.1';
$port = 5000;
$connection = @fsockopen($host, $port);

if (is_resource($connection)) {
    fclose($connection);
    echo 'ready';
} else {
    echo 'not ready';
}
