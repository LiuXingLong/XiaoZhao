<?php
require_once('Base.class.php');
class Hnust extends Base{
	public function __construct() {
		Base::__construct();
		$this->url = HNUST_URL;
		$this->url_data = HNUST_URL_DATA;
		$this->url_info = HNUST_URL_INFO;
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
			$this->getinfo(true,true,'招聘会信息','日访问量');
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
		$str = file_get_contents($this->url);
		$pattern = '/\d\/[0-9]{1,2}/';
		preg_match_all($pattern,$str,$page);
		$page = explode('/',$page[0][0]); //   宣讲会页数信息
		for($i=$page[0]; $i<=$page[1]; $i++){
			$nodes[] = $this->url.'&pageNo='.$i;
		}
		$nodes = $this->multiple_threads_request($nodes);   // 获取每页内容
		//提取每一页中含有 宣讲会的信息  存入  $this->data;
		$pattern = '/(?:<a href="javaScript:show_jqzph.*<\/a>)|(?:\d\d\d\d-\d\d-\d\d \d\d:\d\d)/';
		$pattern1 = '/(?:[a-z0-9]{24})|(?:\[.* )/';
		for($i=$page[0],$k=0; $i<=$page[1]; $i++){
			$str = $nodes[$i-1];
			$str = mb_convert_encoding($str, 'utf-8', 'gbk');
			preg_match_all($pattern,$str,$out);
			$count = count($out[0]);
			for($j=0;$j<$count;$j+=2){				
				preg_match_all($pattern1,$out[0][$j],$name);
				$this->data[$k][0] = $name[0][1];
				$this->data[$k][1] = $out[0][$j+1];
				$this->data[$k][2] = $name[0][0];
				$this->info[] = $this->url_data.$this->data[$k++][2];
			}
		}
	}
}