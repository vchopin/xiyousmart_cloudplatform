<?php
class AccountAction extends CommonAction{
    //修改用户名、邮箱、密码
    public function updateInfo(){
    	$this->type = I('type');
    	$uid = $_SESSION['uid'];
    	$user = M('user')->where('id='.$uid)->field('username,email')->find();
    	$this->display();
    }

    public function updateInfoHandle(){
    	$uid = $_SESSION['uid'];
    	$username = I('username');
    	$email = I('email');
    	$password = I('password','','md5');
    	$password1 = I('password1','','md5');
    	$password2 = I('password2','','md5');
    	
    	$isOk = false;
    	if(M('user')->where(array('id'=>$uid,'password'=>$password))->find()){
    		
    		if($username!='')
    			$isOk = M('user')->where('id='.$uid)->save(array('username'=>$username));
    		if($email!=''){
    			if(!$hademail = M('user')->where(array('email'=>$email))->find())
    			$isOk = M('user')->where('id='.$uid)->save(array('email'=>$email));
    		}
    		if($password1!=''&&($password1==$password2)){
    			$isOk = M('user')->where('id='.$uid)->save(array('password'=>$password1));
                $url = U('Admin/Login/logout');
    		    if($isOk) $this->success("密码修改成功,请重新登陆",$url);
    		    exit;
    		}

    	}else{
    		$this->error("密码验证错误");
    	}
    	$url = U('Home/Account/userInfo');
    	if($isOk) $this->success("修改成功",$url);
    	else if($hademail) $this->error("已存在该邮箱，请更换。",U('Home/Account/updateinfo').'?type=email');
    		else $this->error("修改出错或没有修改",$url);

    }

    //用户登陆基本信息
    public function userInfo(){
    	//没登陆，转跳登陆
    	if(!isset($_SESSION[C('USER_AUTH_KEY')])){
			$this->redirect('Admin/Login/index');
		}

    	$uid = $_SESSION['uid'];
    	$this->user = M('user')->where('id='.$uid)->field('password',true)->find();
        $this->show();
    }





    //完善用户信息的任务
	public function userDetailInfo(){
		/*$uid = $_SESSION['uid'];
		$task_id = I('task_id');
		$this->task_ok = M('task_ok')->where(array('uid'=>$uid,'task_id'=>$task_id))->find();
		$userinfo = M('userinfo')->where(array('uid'=>$uid))->select();
		$userinfo = $userinfo[0];
		//获取学院专业年级的信息
		$this->college = M('class')->where(array('flag'=>1))->select();
		$this->major = M('class')->where('fid='.$userinfo['college'])->select();
		$this->class = M('class')->where('fid='.$userinfo['major'])->select();

        //将学院专业班级的id转换为名字
		$userinfo = class_convert($userinfo);
		//将家庭住址按省、市、区分开
        $home = explode('_',$userinfo['home']);
        $this->assign('userinfo',$userinfo);  
		$this->assign('home',$home); //加入home数组标签中*/
		$this->display(); 
	}
	//Ajax用户信息录入
    public function userInfoHandle(){
    	$uid =  $_SESSION['uid'];
    	$task_id = I('task_id');
    	$status = M('userinfo')->where(array('uid'=>$uid))->getField('status');
		$data=array(
            'uid' => $uid,
			'name' => I('name'),
		    'sex' => I('sex'),
		    'nation' => I('nation'),
		    'number' => I('number'),
		    'entrance' =>I('entrance'),
		    'college' => I('college'),
		    'major' => I('major'),
		    'class' => I('class'),
		    'birthday' => I('birthday'),
		    'idcard' => I('idcard'),
		    'home' => I('s_province').'_'.I('s_city').'_'.I('s_county'),
		    'post' => I('post'),
		    'phone' => I('phone'),
		    'qq' => I('qq'),
		    'email' =>I('email')
			);
		$userinfoReturn=M('userinfo')->where('uid='.$uid)->save($data);
		$task_line = array(
			'uid' => $uid,
			'task_id' => $task_id,
			'title' => "更新个人资料",
			'content' => '',
			'status' => $status,
			'time' => time(),
			'type' => 1
			);
		M('task_line')->add($task_line);
		$this->ajaxReturn($userinfoReturn,'json');
	

	}
	public function college(){
		$col=M('class')->where(array('fid'=>I('col')))->select();
		$this->ajaxReturn($col,'json');
	}
	public function major(){
		$col=M('class')->where(array('fid'=>I('col')))->select();
		$this->ajaxReturn($col,'json');

	}
}
?>