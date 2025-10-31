<?php
session_start();
$host = "localhost"; 
$user = "root";
$pswrd = "";
$dbname = "abc_college_db";
$port = 3307;

$conn = mysqli_connect($host, $user, $pswrd, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function debug($str)
{
    echo "<pre>";
    print_r($str);
    echo "</pre>";
} 	
?>