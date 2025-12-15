<?php
// debug_users.php
$config = [
    "db_host" => "nautilus-db",
    "db_port" => "3306",
    "db_name" => "nautilus",
    "db_user" => "root",
    "db_pass" => "Frogman09!"
];

$mysqli = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port']);

if ($mysqli->connect_error) {
     die("Connection failed: " . $mysqli->connect_error);
}

echo "<h1>Users</h1>";
$result = $mysqli->query("SELECT u.id, u.email, u.first_name, u.last_name, r.name as role_name FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id");
echo "<table border=1><tr><th>ID</th><th>Email</th><th>Name</th><th>Role</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    foreach ($row as $val) echo "<td>$val</td>";
    echo "</tr>";
}
echo "</table>";
