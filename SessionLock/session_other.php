<?php
session_start();

if (isset($_SESSION['hCount'])) {
   echo $_SESSION['hCount']++;
}else {
   echo $_SESSION['hCount'] = 1;
}

