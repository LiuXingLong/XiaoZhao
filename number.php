<?php
$config['host'] = '127.0.0.1';       // redis 服务器 IP
$config['port'] = '6379';            // redis 服务器 端口
define('REDIS_HOST',$config['host']);
define('REDIS_PORT',$config['port']);

$redis = new Redis();
$redis->connect(REDIS_HOST,REDIS_PORT);
$number = $redis->get('number');
$redis->close();
echo '今天的访问量为：',$number;