<?php
require_once '../config/database.php';

echo "PHP Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Timezone: " . date_default_timezone_get() . "\n";

$stmt = $pdo->query("SELECT NOW() as db_time, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
$row = $stmt->fetch();

echo "DB Time: " . $row['db_time'] . "\n";
echo "DB Global TZ: " . $row['global_tz'] . "\n";
echo "DB Session TZ: " . $row['session_tz'] . "\n";
?>
