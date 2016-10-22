<?php
require_once('Base.class.php');
class Csu extends Base{
	private $typeid;          //  学院编号
	private $pagesize;        //  一页请求数量
	/**
	 * 初始化信息
	 */
	public function __construct() {
		Base::__construct();
		$this->url = CSU_URL;
		$this->url_data = CSU_URL_DATA;
		$this->url_info = CSU_URL_INFO;
	}
	/**
	 * 获取提交信息的查询结果
	 * @param unknown $vocation  查询词
	 * @param unknown $school    查询学校
	 * @param unknown $time      查询时间
	 * @param unknown $page      查询页数
	 * @param unknown $flag      标志   1代表刷新   0代表操作    2代表系统脚本更新数据
	 * @return string            结果值
	 */
	public function index($vocation,$school,$time,$page,$flag){
		$id = explode("_",$school);
		if($id[1]==1){
			$this->pagesize = 450;
		}else{
			$this->pagesize = 150;
		}
		$this->typeid = $id[1];
		$this->vocation = $vocation;
		$this->school = $school;
		$this->time = $time;
		$this->page = $page;
		if($flag==1) $this->number(); // 记录访问量
		if(false===$this->detection($this->school)||$flag==2){			
			// 数据过期
			$this->getdata();
			if($this->typeid == 1){
				$this->getinfo(false,true,'文章内容','CopyRight');
			}else{
				$this->getinfo(false,true,'文章内容','相关文章');
			}
			if($flag==2) return;
			$this->getvalue();
			return $this->pagevalue(15);
		}else{
			$this->getvalue();
			return $this->pagevalue(15);
		}
	}
	/**
	 * curl post 请求
	 * @param unknown $url       请求url
	 * @param unknown $pagesize  post数组中的pagesize值
	 * @param unknown $typeid    post数组中的typeid值
	 */
	private function post_request($url,$pagesize,$typeid){		
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$options=array(
			'Referer:http://jobsky.csu.edu.cn/Home/ArticleList/'.$typeid,
			'User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36'
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$options);
		$postData="pageindex=1&pagesize=".$pagesize."&typeid=".$typeid."&followingdates=-1";
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result; 
	}
	/**
	 * 获取数据存入  $this->data 中
	 */
	private function getdata(){		
		$nodes = array();
		$nodes[0] = $this->post_request($this->url,$this->pagesize,$this->typeid);
		//提取每一页中含有 宣讲会的信息  存入  $this->data;	
		$len = count($nodes);
		$date = date("Y.m.d",time());
		$pattern = '/(?:\/.*\/.*\/[0-9]{1,})|(?:>.*<\/a>)|(?:\d\d\d\d\.\d\d\.\d\d)/';
		for($i=0,$k=0; $i<$len; $i++){
			$str = $nodes[$i];		
			//$str = mb_convert_encoding($str, 'utf-8', 'gbk');
 			preg_match_all($pattern,$str,$out);				
 			$count = count($out[0]);		
 			for($j=0;$j<$count;$j+=3){
 				if($date > $out[0][$j+2]) break;
 				$this->data[$k][0] = substr($out[0][$j+1],1,-4); 				
 				$this->data[$k][1] = str_ireplace('.','-',$out[0][$j+2]);			
 				$this->data[$k][2] = $out[0][$j];
 				$this->info[] = $this->url_data.$this->data[$k++][2];
 			} 
		}
	}
}