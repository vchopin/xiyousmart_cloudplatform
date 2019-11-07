<?php
/**
 * 后台登陆控制器
 */
Class LoginAction extends Action{
	/**
	 * 登陆视图
	 */
	Public function index(){
		$this->display();
	}

	Public function login(){
		if(!IS_POST) halt('页面不存在');
		
		if(I('code','','md5')!=$_SESSION['verify']){
			$this->error('验证码错误');
		}
		$username = I('username');
		$pwd = I('password','','md5');

		$user = M('user')->where(array('username'=>$username,'email'=>$username,'_logic'=>'OR'))->find();
		if(!$user||$user['password'] !=$pwd){
			$this->error('账户或密码错误');
		}

		if($user['lock']==1) $this->error('用户被锁定');

		$data = array(
			'id'=> $user['id'],
			'login_ip' => get_client_ip(),
			'logintime' => time(),
			);

		M('user')->save($data);

		session(C('USER_AUTH_KEY'),$user['id']);
		session('username',$user['username']);
		session('logintime',date('Y-m-d H:i:s',$user['logintime']));
		session('loginip',$user['loginip']);


        //超级管理员识别
		if ($user['username']==C('RBAC_SUPERADMIN')) {
			session(C('ADMIN_AUTH_KEY'),true);
		}


		import('ORG.Util.RBAC');
		RBAC::saveAccessList();

        $manager['_query'] = 'name=manager&_logic=or';
        $isManager = false;
        $role = M('role')->where($manager)->getField('id',true);
        foreach ($role as $v) {
        	if(M('role_user')->where(array('role_id'=>$v,
        		                           'user_id'=>$_SESSION['uid'],
        		                           '_logic' =>'AND'
        		                     ))->find())
        		$isManager = true;
        }
        //var_dump($isManager);die;
		if($isManager == true){
			$this->redirect('Admin/Index/index');
		}else{
			$this->redirect('Index/Index/index');
		}

		


	}

	Public function verify(){
		import('ORG.Util.Image');
		Image::buildImageVerify(4,1,'png',80,25,'verify');// 参数：长度，类型，图片格式，宽，高，session名称

	}
	//Ajax验证
	public function ckusername(){
		
		if(M('user')->where(array('username'=>I('username'),'email'=>I('username'),'_logic'=>'OR'))->find())
			$stat=1;
		else $stat=0; 
		$this->ajaxReturn($stat,'json');
		
	}
	public function ckpassword(){
		$pwd = I('password','','md5');
		$user = M('user')->where(array('username'=>I('username'),'email'=>I('username'),'_logic'=>'OR'))->find();
		if($user['password'] == $pwd){
			$stat=1;
		}else{
			$stat=0;
		}
		$this->ajaxReturn($stat,'json');

		
	}
	public function ckverify(){
		if(I('code','','md5')!=$_SESSION['verify'])
			$stat=0;
		else $stat=1;
		$this->ajaxReturn($stat,'json');
		
	}
	
	public function logout(){
			session_unset();
			session_destroy();
			$this->redirect('Home/Login/index');
		}

		

    public function register(){
    	
		//获取学院专业年级的信息
		$this->college = M('class')->where(array('flag'=>1))->select();
		//p($college);

        //将学院专业班级的id转换为名字
		$userinfo = class_convert($userinfo);
		$begin = M('config')->where(array('name'=>'freshman'))->getField('options');
		$entrance = array($begin,$begin-1,$begin-2,$begin-3) ;
		//p($entrance);
		$this->entrance = $entrance;
    	$this->show();
    	
    }
    public function college(){
		$col=M('class')->where(array('fid'=>I('col')))->select();
		$this->ajaxReturn($col,'json');
	}
	public function major(){
		$col=M('class')->where(array('fid'=>I('col')))->select();
		$this->ajaxReturn($col,'json');
	}
	public function registerHandle(){
		if(M('user')->where(array('username'=>I('number')))->select())
			{$this->ajaxReturn('-1','json');die;}
		if(M('user')->where(array('email'=>I('email')))->select())
			{$this->ajaxReturn('-2','json');die;}
		$user=array(
			'username' => I('number'),
			'password' => I('qq','','md5'),
			'email'    => I('email')
			);
		$uid = M('user')->add($user);

		$userinfo = array(
            'uid' => $uid,
			'name' => I('name'),
		    'sex' => I('sex'),
		    'number' => I('number'),
		    'entrance' =>I('entrance'),
		    'college' => I('college'),
		    'major' => I('major'),
		    'class' => I('class'),
		    'phone' => I('phone'),
		    'qq' => I('qq'),
		    'email' =>I('email')
			);
		if(M('userinfo')->where(array('number'=>I('number')))->select())
			$Return=M('userinfo')->where(array('number'=>I('number')))->save($userinfo);
        else
		$Return=M('userinfo')->add($userinfo);

		$this->ajaxReturn($Return,'json');
		/*$url = U('Index/Index/index');
    	if($Return) $this->success("注册成功",$url);
    	else $this->error("注册失败，请重试或与管理员联系");*/
	}
}
?>   