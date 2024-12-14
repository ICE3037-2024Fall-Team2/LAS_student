<?php
session_start(); 


session_unset();


session_destroy();


header("Location: ../student_pages/login.php");
exit();
?>
