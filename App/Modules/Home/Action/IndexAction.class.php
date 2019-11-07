<?php
class IndexAction extends CommonAction{
	public function  index(){
		$this->show();
	}
	public function userTaskList(){
		$task_comp = I('task_comp',-1,'intval');
		$uid = $_SESSION['uid'];
		if(!is_int($task_comp)||$task_comp==-1) $task_comp = array('EXP','IS NOT NULL');

		$check_condition = array(
			'task_comp' => $task_comp,
			'uid' => $uid,
			'_logic' => 'AND'
			);


		//分页列出汇总数据
		//import('ORG.Util.Page');
		$count = D('CheckListView')->where($check_condition)->count();
        
		//$page = new Page($count,20);
		//$limit = $page->firstRow .','. $page->listRows;
		//用框架分页
		$limit = I('start',0,'intval').','.I('limit',0,'intval');
	    

		$check = D('CheckListView')->where($check_condition)->order('number')->limit($limit)->select();
        /*foreach ($check as $key => $value) {
        	foreach ($value as $k => $v) {
        		if($k == 'post_time') $check[$key][$k] = date('Y-m-d',$check[$key][$k]);
        	}
        	
        }*/
        //p($check);die;
	
	
	//必须确保返回数据名称是双引号
        $json1='{"rows":[';
        $json2='';
        foreach ($check as $key => $value) {
        	$value['title'] = "<a class='various' href='".U('Index/Task/dotask')."?task_id=".$value['task_id']."'>".$value['title']."</a>";
        	$temp='{';
        	foreach ($value as $k => $v){
        		//转换时间格式
        		if($k=='post_time'||$k=='check_time'){
        			$v=date('Y-m-d',$v);
        		}
        		if($k=='content') continue; //不太清楚为什么，返回数据中有content就不行，可能和bui冲突
        		else
        		$temp=$temp.'"'.$k.'":"'.$v.'",';
          }
          $json2=$json2.substr($temp, 0, -1).'},';
        	# code...
        }
        
        $json3 = '],"results": '.$count.'}';
        $json = $json1.substr($json2, 0, -1).$json3;
        
         echo $json;
	}
}

?>