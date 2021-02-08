<?php
require_once "classes/mysql.php";

$database = new mysql;
$database->Connect($database_server,$database_port,$database_database,$database_user,$database_password);

session_start();
?>