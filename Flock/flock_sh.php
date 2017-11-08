<?php
# 读锁
$lockfile = "./lock";

$fp = fopen($lockfile, "w");

if (flock($fp, LOCK_SH)) {
    echo 'Shard Locking...' . "\n";

    sleep(30);
    
    echo 'Read finished!'. "\n";

    flock($fp, LOCK_UN);
} else {
    echo "Cound't get the shard lock... \n";    
}


