<?php
require_once('Base.class.php');
class Xtu extends Base{
	public function __construct() {
		Base::__construct();
		$this->url = XTU_URL;
		$this->url_data = XTU_URL_DATA;
		$this->url_info = XTU_URL_INFO;
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
		$this->vocation = $vocation;
		$this->school = $school;
		$this->time = $time;
		$this->page = $page;
		if($flag==1) $this->number(); // 记录访问量
		if(false===$this->detection($this->school)||$flag==2){
			// 数据过期
			$this->getdata();
			$this->getinfo(true,true,'阅读文章','上一篇');
			if($flag==2) return;
			$this->getvalue();
			return $this->pagevalue(15);			
		}else{
			$this->getvalue();
			return $this->pagevalue(15);
		}
	}
	/**
	 * 获取数据存入  $this->data 中
	 */
	private function getdata(){		
		$nodes = array();
		for($i=1; $i<=30; $i++){
			$nodes[] = $this->url.$i;
		}
		$nodes = $this->multiple_threads_request($nodes);   // 获取每页内容
		
		//提取每一页中含有 宣讲会的信息  存入  $this->data;
		$len = count($nodes);
		$date = date("Y-m-d",time());
		$pattern = '/trbg">.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*\n.*<\/tr>/';
		$pattern1 = '/(?:showarticle\.php.*keyword=)|(?:title="">.*<\/a>)|(?:\d\d\d\d-\d\d-\d\d)/';
		for($i=0,$k=0; $i<$len; $i++){
			$str = $nodes[$i];
			$str = mb_convert_encoding($str, 'utf-8', 'gbk');
			preg_match_all($pattern,$str,$out);
			$count = count($out[0]);
			for($j=0;$j<$count;$j++){
				preg_match_all($pattern1,$out[0][$j],$name);
				if($date > $name[0][2]) continue;
				$this->data[$k][0] = substr($name[0][1],9,-4);
				$this->data[$k][1] = $name[0][2];
				$this->data[$k][2] = $name[0][0];			
				$this->info[] = $this->url_data.$this->data[$k++][2];	
			}	
		}
	}
}