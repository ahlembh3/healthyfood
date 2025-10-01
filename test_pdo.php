<?php
var_dump(extension_loaded("pdo_mysql"));
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=mysql;charset=utf8mb4","root","");
echo "OK\n";
