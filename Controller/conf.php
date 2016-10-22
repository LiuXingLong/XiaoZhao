<?php
$config['host'] = '127.0.0.1';       // redis 服务器 IP  
$config['port'] = '6379';            // redis 服务器 端口
$config['time'] = 86400;             // redis 过期时间为24小时
define('REDIS_HOST',$config['host']);
define('REDIS_PORT',$config['port']);
define('REDIS_TIME',$config['time']);

/**
 * 就业信息网url
 */
$config['hnust_url'] = 'http://stu.hnust.edu.cn/jy/jiuyeIndex.do?method=toCategoryZPH&byzd=TYPE16';
$config['xtu_url'] = 'http://xtu.good-edu.cn/showmore.php?actiontype=5&pg=';
$config['csu_url'] = 'http://jobsky.csu.edu.cn/Home/PartialArticleList';
$config['hnu_url'] = 'http://scc.hnu.edu.cn/newsjob!getMore.action?p.currentPage=';
$config['hunnu_url'] = 'http://job.hunnu.edu.cn/module/getcareers?start=0&keyword=&type=inner&day=&count=';
$config['csust_url'] = 'http://www.cslgzj.com/module/getcareers?start=0&keyword=&type=inner&day=&count=';

define('HNUST_URL',$config['hnust_url']);
define('XTU_URL',$config['xtu_url']);
define('CSU_URL',$config['csu_url']);
define('HNU_URL',$config['hnu_url']);
define('HUNNU_URL',$config['hunnu_url']);
define('CSUST_URL',$config['csust_url']);


/**
 * 获取宣讲会页面数据的 url
 */
$config['hnust_url_data'] = 'http://stu.hnust.edu.cn/jy/jiuyeIndex.do?method=showZphInfo2&id=';
$config['xtu_url_data'] = 'http://xtu.good-edu.cn/';
$config['csu_url_data'] = 'http://jobsky.csu.edu.cn';
$config['hnu_url_data'] = 'http://scc.hnu.edu.cn/';
$config['hunnu_url_data'] = 'http://job.hunnu.edu.cn/detail/getcareerdetail?career_id=';
$config['csust_url_data'] = 'http://www.cslgzj.com/detail/getcareerdetail?career_id=';

define('HNUST_URL_DATA',$config['hnust_url_data']);
define('XTU_URL_DATA',$config['xtu_url_data']);
define('CSU_URL_DATA',$config['csu_url_data']);
define('HNU_URL_DATA',$config['hnu_url_data']);
define('HUNNU_URL_DATA',$config['hunnu_url_data']);
define('CSUST_URL_DATA',$config['csust_url_data']);


/**
 * 获取宣讲会页面的 url
 */
$config['hnust_url_info'] = 'http://www.liuxinlong.cn/jy/jiuyeIndex.do?method=showZphInfo2&id=';
$config['xtu_url_info'] = 'http://xtu.good-edu.cn/';
$config['csu_url_info'] = 'http://jobsky.csu.edu.cn';
$config['hnu_url_info'] = 'http://scc.hnu.edu.cn/';
$config['hunnu_url_info'] = 'http://job.hunnu.edu.cn/detail/career?id=';
$config['csust_url_info'] = 'http://www.cslgzj.com/detail/career?id=';

define('HNUST_URL_INFO',$config['hnust_url_info']);
define('XTU_URL_INFO',$config['xtu_url_info']);
define('CSU_URL_INFO',$config['csu_url_info']);
define('HNU_URL_INFO',$config['hnu_url_info']);
define('HUNNU_URL_INFO',$config['hunnu_url_info']);
define('CSUST_URL_INFO',$config['csust_url_info']);

