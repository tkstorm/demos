<?php
# 写锁
$lockfile = "./lock";

$fp = fopen($lockfile , "w");

if (flock($fp ,  LOCK_EX )) {   // 进行排它型锁定
    echo "Write locking.. \n";
    //doing something
    sleep(20);

    flock($fp ,  LOCK_UN );     // 释放锁定
    fclose($fp);
 } else {
    echo  "Couldn't get the lock! \n" ;
}

