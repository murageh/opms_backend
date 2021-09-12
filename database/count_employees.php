<?php

require_once "connect.php";

$result = mysqli_query($mysqli, "select count(1) FROM employees");
$row = mysqli_fetch_array($result);

$total = $row[0];
$data = ['count' => $total];
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
exit();

mysqli_close($mysqli);
