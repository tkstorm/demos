<?php
session_start();

if (isset($_SESSION['hCount'])) {
   echo $_SESSION['hCount']++;
}else {
   echo $_SESSION['hCount'] = 1;
}

//do some thing.. block
if ( isset($_GET['close_session']) ) {
    echo "\n session_write_close \n";
    session_write_close();
}

sleep(5);



