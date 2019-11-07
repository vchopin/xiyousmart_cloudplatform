<?php
class UserTaskListViewModel extends ViewModel {
	
	public $viewFields = array(
     'task_ok'=>array('id'=>'task_ok_id','uid','task_comp','task_id', 'checker_id','check_time','check_result','check_feedback','post_time'),
     'task' =>array('title','check_url','status','_on'=>'task.id=task_ok.task_id'),
     'userinfo' =>array('name','_on'=>'userinfo.uid=task_ok.checker_id')
   );
}
?>