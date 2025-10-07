<?php
session_start();
ini_set('display_errors', '1');
$host = "localhost";
$dbUser = "root";
$dbpass = "";
$db = "abc_college_db";

$conn = mysqli_connect($host,$dbUser,$dbpass,$db);

if($conn){
    // echo "Connection Successfully";
}else{
    // echo "some error";
}


function debug($str){
    echo "<pre>";
    print_r($str);
    echo "</pre>";
}

?>