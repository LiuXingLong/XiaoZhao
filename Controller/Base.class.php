<?php
require_once('conf.php');
Class Base {
	private $redis;             //  Redis 对象
	private $redis_time;        //  缓存时间	
	protected $url;             //  就业信息网  url
	protected $url_data;        //  获取宣讲会页面数据的 url
	protected $url_info;        //  获取宣讲会页面的 url
	protected $school;          //  学校作$key
	protected $vocation;        //  查询职位关键词
	protected $time;            //  查询时间
	protected $page;            //  查询页数
	protected $data = array();  //  所有宣讲会的       公司名字     举办时间     url
	protected $info = array();  //  单独宣讲会信息
	protected $value = array(); //  根据查询条件因返回的宣讲会信息   包含    公司名字    举办时间    url
	protected function __construct() {
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST,REDIS_PORT);
		$this->redis_time = REDIS_TIME;
	}
	/**
	 * 访问量加一
	 */
	protected function number(){
		if($this->redis->exists('number')==0){
			$this->redis->set('number',1);
			//$this->redis->expire('number',$this->redis_time);
		}else{
			$number = $this->redis->get('number');			
			$this->redis->set('number',++$number);				
		}
	}
	/**
	 * 检测指定的key是否在Redis中,不在返回 false 否则返回 true
	 * @param unknown $key  需检测的key
	 */
	protected function detection($key){
		if($this->redis->exists($key.'_flag') == 0){
			return false;
		}
		return true;
	}
	/**
	 * 存取数据到 Redis
	 * @param unknown $key    存入的键值
	 * @param unknown $value  存入的值
	 */
	protected function setcontent($key,$value){
	    if(!empty($value)){
	    	$array = array($key.'_flag' => 1, $key => serialize($value));
	    	$this->redis->mset($array);
	    	//$this->redis->expire($key.'_flag',$this->redis_time);
	    	//$this->redis->expire($key,$this->redis_time);
	    }    
	}
	/**
	 * 获取 Redis 中的数据
	 * @param unknown $key  获取的键值
	 * @return mixed  获取值序列化后的值
	 */
	protected function getcontent($key){
		return unserialize($this->redis->get($key));
	}
	/**
	 * 截取指定两个字符串之间的字符串
	 * @param unknown $begin  开始字符串
	 * @param unknown $end    结束字符串
	 * @param unknown $str    要截取的字符串
	 * @return string         截取后的字符串
	 */
	protected function str_cut($begin,$end,$str){
		$start = mb_strpos($str,$begin) + mb_strlen($begin);
		$len = mb_strpos($str,$end) - $start;
		return mb_substr($str,$start,$len);
	}
	/**
	 * 将二维数组按第一个字段按降序排列返回
	 * @param unknown $a
	 * @param unknown $b
	 */
	protected function my_sort($a,$b){
		if ($a[1]==$b[1]) return 0;
		return ($a[1]>$b[1])?1:-1;
	}
	/**
	 * curl多线程请求数据
	 * @param unknown $nodes     需请求的 curl 数组
	 * @return multitype:string  获取到数据的数组
	 */
	protected function multiple_threads_request($nodes){
		$mh = curl_multi_init();
		$curl_array = array();
		foreach($nodes as $i => $url) {
			$curl_array[$i] = curl_init($url);
			curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
			// 设置代理  IP
			curl_setopt($curl_array[$i], CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
			curl_setopt($curl_array[$i], CURLOPT_PROXY, "202.106.16.36:3128"); //代理服务器地址
			curl_setopt($curl_array[$i], CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
			curl_multi_add_handle($mh, $curl_array[$i]);
		}
		$running = NULL;
		do {
			usleep(10000);
			curl_multi_exec($mh,$running);
		} while($running > 0);
		$res = array();
		foreach($nodes as $i => $url) {			
			$res[] = curl_multi_getcontent($curl_array[$i]);
		}
		foreach($nodes as $i => $url) {
			curl_multi_remove_handle($mh, $curl_array[$i]);
		}
		curl_multi_close($mh);
		return $res;
	}
	/**
	 * 获取所有宣讲会中的信息
	 * @param unknown $flag_utf_8         是否需要 gbk转utf_8
	 * @param unknown $flag_sort    是否需要对时间排序
	 * @param unknown $begin        获取信息的    开始   部分
	 * @param unknown $end          获取信息的    结束   部分
	 */
	protected function getinfo($flag_utf_8,$flag_sort,$begin,$end){
		$this->info = $this->multiple_threads_request($this->info);
		$count = count($this->info);
		$patterns = array('/[\s\t\n]{0,}/','/[a-z]+:\/\/[a-z0-9_\-\/.%]+/','/\d\d\d\d-\d\d-\d\d/');  // 空白字符  , url , 时间             
		for($i = 0; $i < $count; $i++){
			$str = $this->info[$i];
			if($flag_utf_8) $str = mb_convert_encoding($str, 'utf-8', 'gbk');
			$str = preg_replace($patterns,'',strip_tags($str));
			$this->data[$i][3] = $this->data[$i][1].$this->str_cut($begin,$end,$str);
		}
		if($flag_sort) usort($this->data,array("Base", "my_sort"));	
		$this->setcontent($this->school,$this->data);
	}
	/**
	 * 查找满足条件的宣讲会并存入到  $this->value 中
	 * @return string  返回值
	 */
	protected function getvalue(){
		if(empty($this->data)){
			$this->data = $this->getcontent($this->school);
		}
		if(!empty($this->vocation)){
			$flag = 0;
			if(false!==stripos($this->vocation,"&")){
				//  同时满足
				$flag = 1;
				$key = explode("&",$this->vocation);
			}else if(false!==stripos($this->vocation,"|")){
				//  满足任一一个
				$flag = 2;
				$key = explode("|",$this->vocation);
			}else{
				$key = trim($this->vocation);
			}
			if($flag!=0){
				$count = count($key);
				for($i = 0; $i<$count; $i++){
					$key[$i] = trim($key[$i]);
				}
			}
			$count = count($key);
			$len = count($this->data);
			for($i=0; $i<$len; $i++){
				if($this->time!=0&&false===stripos($this->data[$i][1], $this->time)) continue;
				if($flag==0) {
					if(false!==stripos($this->data[$i][3], $key)){
						$this->value[] = $this->data[$i];
					}
				}else if($flag==1){
					$j=0;
					for(; $j<$count; $j++) {
						if(false===stripos($this->data[$i][3], $key[$j])) break;
					}
					if($j==$count){
						$this->value[] = $this->data[$i];
					}
				}else{
					for($j=0; $j<$count; $j++) {
						if(false!==stripos($this->data[$i][3], $key[$j])){
							$this->value[] = $this->data[$i];
							break;
						}
					}
				}
			}
		}else if($this->time!=0){
			$len = count($this->data);
			for($i=0; $i<$len; $i++){
				if(false!==stripos($this->data[$i][1], $this->time)){
					$this->value[] = $this->data[$i];
				}
			}
		}else{
			$this->value = $this->data;
		}
	}
	/**
	 * 将查询到的   $this->value 数据进行分页封装返回 
	 * @param unknown $num  每页显示条数
	 * @return string       返回数据
	 */	
	protected function pagevalue($num){
		// 封装分页
		$str = "";
		$count = count($this->value);		
		$count = ceil($count/$num); //总页数         ceil()向上取整
		if($this->page==-1){ 
			$this->page = 1;       // 首页
		}else if($this->page==-2){
			$this->page = $count;  // 尾页 
		}else if($this->page < 1){
			$this->page = 1;       // 上一页
		}else if($this->page > $count&&$count!=0){
			$this->page = $count;  // 下一页 
		}
		if($this->page==1||$count==0){
			$str.='<li class="disabled"><a href="javascript:volid(0);">首页</a></li>
					<li class="disabled"><a href="javascript:volid(0);">上一页</a></li>';
		}else{
			$str.='<li><a href="#">首页</a></li>
					<li><a href="#">上一页</a></li>';
		}
		if($count<=10){
			for($i = 1; $i<=$count; $i++){
				if($i==$this->page){
					$str.='<li class="active"><a href="javascript:volid(0);">'.$i.'</a></li>';						
				}else{
					$str.='<li><a href="#">'.$i.'</a></li>';
				}	
			}
		}else{
			$start = $this->page-5;
			$end = $this->page+4;
			if($start<1){
				$start = 1;
				$end = $start+9;
			}else if($end>$count){
				$end = $count;
				$start = $end-9;
			}
			for($i = $start; $i<=$end; $i++){
				if($i==$this->page){
					$str.='<li class="active"><a href="javascript:volid(0);">'.$i.'</a></li>';						
				}else{
					$str.='<li><a href="#">'.$i.'</a></li>';
				}
			}
		}
		if($this->page==$count||$count==0){
			$str.='<li class="disabled"><a href="javascript:volid(0);">下一页</a></li>
					<li class="disabled"><a href="javascript:volid(0);">尾页</a></li>';
		}else{
			$str.='<li><a href="#">下一页</a></li>
					<li><a href="#">尾页</a></li>';
		}

		// 封装返回vaule信息
		$str1 = "";
		$start = ($this->page-1)*$num;
		$this->value = array_slice($this->value,$start,$num); 
		$count = count($this->value);
		for($i=0; $i<$count; $i++){
			if($i%2==0){
				$str1.='<tr>';
			}else {
				$str1.='<tr class="success">';
			}
			$str1.='<td>'.($i+1).'</td>
				   <td><a href='.$this->url_info.$this->value[$i][2].' target="_blank">'.$this->value[$i][0].'</a></td>
			       <td>'.$this->value[$i][1].'</td>
			       </tr>';
		}
		return $str1.','.$str.','.$this->vocation;  
	}
}