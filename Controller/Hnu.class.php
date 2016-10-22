<?php
require_once('Base.class.php');
class Hnu extends Base{
	public function __construct() {
		Base::__construct();
		$this->url = HNU_URL;
		$this->url_data = HNU_URL_DATA;
		$this->url_info = HNU_URL_INFO;
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
			$this->getinfo('登录!登录','|关闭|');
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
		for($i=1; $i<=35; $i++){
			$nodes[] = $this->url.$i.'&Lb=1';
		}
		$nodes = $this->multiple_threads_request($nodes);   // 获取每页内容
		//提取每一页中含有 宣讲会的信息  存入  $this->data;
		$flag = 0;
		$date = date("Y/m/d",time());
		$pattern = '/(?:<a.*articledetail\?t\.PostId=[0-9]{0,})|(?:\d\d\d\d\/\d\d\/\d\d)/';
		$pattern1 = '/(?:".*" )|(?:articledetail\?t\.PostId=[0-9]{0,})/';
		for($i=1,$k=0; $i<=35; $i++){
			if($flag==1) break;
			$str = $nodes[$i-1];
			//$str = mb_convert_encoding($str, 'utf-8', 'gbk');
			preg_match_all($pattern,$str,$out);
			$count = count($out[0]);
			for($j=0;$j<$count;$j+=2){
				if($date > $out[0][$j]){
					$flag = 1; 
					break;// 招聘会过期				
				}
				preg_match_all($pattern1,$out[0][$j+1],$name);
				$this->data[$k][0] = substr($name[0][0],1,-2);
				$this->data[$k][1] = str_ireplace('/','-',$out[0][$j]);
				$this->data[$k][2] = $name[0][1];
				$this->info[] = $this->url_data.$this->data[$k++][2];
			}
		}
	}
	/**
	 * 重写Base类    getinfo()
	 * 获取所有宣讲会中的信息
	 */
	protected function getinfo($begin,$end){
		$count = count($this->info);
		$info1 = array_chunk($this->info,100);
		$cnt = count($info1);
		$this->info = array();
		for($i=0;$i<$cnt;$i++){
			$info[$i] = $this->multiple_threads_request($info1[$i]);			
			$this->info = array_merge($this->info,$info[$i]);
		}		
		$count = count($this->info);
		$patterns = array('/[\s\t\n]{0,}/','/[a-z]+:\/\/[a-z0-9_\-\/.%]+/','/\d\d\d\d-\d\d-\d\d/');  // 空白字符  , url , 时间             
		for($i = 0; $i < $count; $i++){
			$str = $this->info[$i];		
			$str = preg_replace($patterns,'',strip_tags($str));
			$this->data[$i][3] = $this->data[$i][1].$this->str_cut($begin,$end,$str);
		}
		usort($this->data,array("Base", "my_sort"));
		$this->setcontent($this->school,$this->data);
	}
}