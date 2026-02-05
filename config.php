<?php
/* Very small reusable DB connector (procedural style) */
/* ------------------------------------------------------------------
 * config.php
 * Purpose: create ONE reusable MySQLi connection ($conn) that every
 *          other PHP script can simply “require_once”.
 * ------------------------------------------------------------------ */

/* Read database credentials from environment variables (provided by Railway) */
$host = getenv('MYSQLHOST')     ?: 'localhost';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'todo';
$port = getenv('MYSQLPORT')     ?: '3306';

/* procedural MySQLi: keeps the code short and dependency-free */

$conn = mysqli_connect($host, $user, $pass, $db, $port);

/* If the connection fails we CAN’T continue – die with a message */

if (!$conn) {
    die('DB connection failed: ' . mysqli_connect_error());
}
/* After this file is included, $conn is ready for prepared statements. */
?>