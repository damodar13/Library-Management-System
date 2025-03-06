<?php
$servername = "localhost";
$username="root";
$password="@Damodar_13";
$dbname="lms";

$conn=new mysqli($servername,$username,$password,$dbname);
if(mysqli->connect_error){
    die("COnnection Failed:".$conn->connect_error);

}
?>
