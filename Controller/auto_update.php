<?php
#!/usr/bin/php -q
ignore_user_abort();     // 即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
set_time_limit(0);       // 执行时间为无限制，php默认的执行时间是30秒，通过set_time_limit(0)可以让程序无限制的执行下去
require_once('Hnust.class.php');
require_once('Xtu.class.php');
require_once('Csu.class.php');
require_once('Csust.class.php');
require_once('Hunnu.class.php');
require_once('Hnu.class.php');


$time = date("H:i",time());
if($time<"06:00"){
	$redis = new Redis();
	$redis->connect(REDIS_HOST,REDIS_PORT);
	$redis->set('number',0);
	$redis->close();
}


$hnust = new Hnust();
$hnust->index('','hnust',0,1,2);


$xtu = new Xtu();
$xtu->index('','xtu',0,1,2);


$csu_1 = new Csu();
$csu_1->index('','csu_1',0,1,2);
$csu_2 = new Csu();
$csu_2->index('','csu_2',0,1,2);
$csu_3 = new Csu();
$csu_3->index('','csu_3',0,1,2);


$csust = new Csust();
$csust->index('','csust',0,1,2);


$hunnu = new Hunnu();
$hunnu->index('','hunnu',0,1,2);


$hnu = new Hnu();
$hnu->index('','hnu',0,1,2);


// $number = $redis->get('number');
// $redis->flushAll();

