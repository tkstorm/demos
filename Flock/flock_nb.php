<?php
# 申请一个写锁, 非阻塞的咨询
$lockfile = "./lock";

$fp = fopen($lockfile , "w");

while(false == flock($fp, LOCK_EX | LOCK_NB)) {
    //尝试等待
    echo "Waiting a second for write lock... \n";

    sleep(1);
};

// 获取到写锁
echo "Good, I got a write lock !! \n";
// 需要10s的锁

sleep(10);

echo "Finished work!! \n";

// 释放锁
flock($fp ,  LOCK_UN );     // 释放锁定
fclose($fp);
