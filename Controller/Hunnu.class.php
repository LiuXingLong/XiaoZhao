<?php
require_once('Base.class.php');
class Hunnu extends Base{
	public function __construct() {
		Base::__construct();
		$count = 150; //查询数量
		$this->url = HUNNU_URL.$count;
		$this->url_data = HUNNU_URL_DATA;
		$this->url_info = HUNNU_URL_INFO;
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
			$this->getinfo();
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
		$str = file_get_contents($this->url);
		$str = json_decode($str);
		$count = count($str->data);
		$date = date("Y-m-d",time());
		foreach($str->data as $k => $value){
			if($date > $value->meet_day) break;		
			$this->data[$k][0] = $value->company_name;
			$this->data[$k][1] = $value->meet_day." ".$value->meet_time;
			$this->data[$k][2] = $value->career_talk_id;		
			$this->info[$k] = $this->url_data.$this->data[$k][2];	
		}
	}
	/**
	 * 重写Base类    getinfo()
	 * 获取所有宣讲会中的信息
	 */
	protected function getinfo(){
		$this->info = $this->multiple_threads_request($this->info);
		$count = count($this->info);
		$patterns = array('/[\s\t\n]{0,}/','/[a-z]+:\/\/[a-z0-9_\-\/.%]+/','/\d\d\d\d-\d\d-\d\d/');  //  空白字符  , url , 时间    
		for($i = 0; $i < $count; $i++){
			$str = $this->info[$i];	
			$str = json_decode($str);
			$str = $str->data->remark;			
			$str = preg_replace($patterns,'',strip_tags($str));
			$this->data[$i][3] = $this->data[$i][1].$str;
		}
		usort($this->data,array("Base", "my_sort"));
		$this->setcontent($this->school,$this->data);
	}
}