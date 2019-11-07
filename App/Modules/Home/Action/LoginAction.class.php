<?php
/**
 * 后台登陆控制器
 */
Class LoginAction extends Action{
	/**
	 * 登陆视图
	 */
	Public function index(){
		$this->redirect('newlogin');
		$this->display();
	}
    Public function newlogin(){
		$this->display();
	}
	Public function login(){
		if(!IS_POST) halt('页面不存在');
		
		if(I('code','','md5')!=$_SESSION['verify']){
			$this->ajaxReturn('codeerror','json');
			$this->error('验证码错误');
		}
		$username = I('username');
		$pwd = I('password','','md5');

		$user = M('user')->where(array('username'=>$username,'email'=>$username,'_logic'=>'OR'))->find();
		if(!$user||$user['password'] !=$pwd){
			$this->ajaxReturn('loginfail','json');
			$this->error('账户或密码错误');
		}

		if($user['lock']==1) {$this->ajaxReturn('locked','json');$this->error('用户还没被激活');}

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
     
        $rid = M('role')->where($manager)->getField('id');
        //p($role);die;
        $isManager = M('role_user')->where(array('role_id'=>$rid,
        		                           'user_id'=>$_SESSION['uid'],
        		                           '_logic' =>'AND'
        		                     ))->find();
        		
      
        //p();die;
        if($_SESSION[C('ADMIN_AUTH_KEY')] == true){
        	$this->ajaxReturn(U('Admin/Index/index'),'json');
        	$this->redirect(U('Admin/Index/index'));
        }else if($isManager == true){
        	$this->ajaxReturn(U('Admin/Index/index').'#200/201','json');
			$this->redirect(U('Admin/Index/index').'#200/201');
		}else{
			$this->ajaxReturn(U('Index/Index/index'),'json');
			$this->redirect(U('Index/Index/index'));
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
	
	Public function logout(){
			session_unset();
			session_destroy();
			$this->redirect('Home/Login/index');
		}
    //邮箱注册
	public function emailregister(){
		if(Config('checkemail')==0) redirect(U('register'));
		$this->show();
	}
	public function emailregisterHandle(){
		   //sendMail('xupingxx@qq.com','党建系统用户注册','测试$sendContent');
		    $email = I('email');
			$code = randCode();
			$url = U('Home/Login/sysredirect',array('code'=>$code),'','',true);
			$sendContent = '点击下面的链接完成注册过程并激活账户<br/><a href="'.$url.'">'.$url.'</a>
							<br/>如果你不知道这是什么东西，说明该邮件为误发，请忽略。';
			$user = M('user')->where(array('email'=>$email))->find();
			if(!$user || $user['lock']==1){
				if( M('user')->add(array('email'=>$email,'username'=>$code,'code'=>$code,'type'=>1)) ||
                    M('user')->where(array('email'=>$email))->save(array('code'=>$code,'type'=>1))
				){//type=1 为用户注册
						if(sendMail($email,'党建系统用户注册',$sendContent)){
							$this->ajaxReturn('sended','json');
						}else{
							$this->ajaxReturn('sendfail','json');
						}
					}else{
						$this->ajaxReturn('dbfail','json');
					}
					
			}else{
				$this->ajaxReturn('hademail','json');
			}
			
	}
		
    public function register(){
    	$code = I('code');
    	$checkemail = Config('checkemail');
    	if($checkemail==1&&$code=='') redirect(U('emailregister'));
    	
		//获取学院专业年级的信息
		$this->college = M('class')->where(array('flag'=>1))->select();
		//p($college);

        //将学院专业班级的id转换为名字
		$userinfo = class_convert($userinfo);
		$begin = freshman();
		$entrance = array($begin,$begin-1,$begin-2,$begin-3) ;
		//p($entrance);
		$this->checkemail = $checkemail;
		$this->code = $code;
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
		$checkemail = Config('checkemail');
		//$this->ajaxReturn($checkemail,'json');die;
		$code = I('code');
		$email = null;
		if($checkemail == '1') {
			$email = M('user')->where(array('code'=>$code,'type'=>1))->getField('email');
		}else $email = I('email');
		
		if(M('user')->where(array('username'=>I('number')))->select())
			{$this->ajaxReturn('-1','json');die;}
		/*if(M('user')->where(array('email'=>I('email')))->select())
			{$this->ajaxReturn('-2','json');die;}*/
		$user=array(
			'username' => I('number'),
			'password' => I('qq','','md5'),
			'code' => null,
			'type' => null,
			'email'=> $email,
			'lock' => 0
			);
		$adduser = M('user');
		$adduser->startTrans();

		if($checkemail == '1') {
			 $adduser->where(array('code'=>$code,'email'=>$email))->save($user);
			 $uid = $adduser->where(array('email'=>$email))->getField('id');
		}
		else $uid = $adduser->add($user);
		

		$userinfo = array(
			'uid'  => $uid,
			'name' => I('name'),
		    'sex' => I('sex'),
		    'number' => I('number'),
		    'entrance' =>I('entrance'),
		    'college' => I('college'),
		    'major' => I('major'),
		    'class' => I('class'),
		    'qq' => I('qq')
			);
		$Return = null;
		if(M('userinfo')->where(array('uid'=>$uid))->select()){
			$Return=M('userinfo')->where(array('uid'=>$uid))->save($userinfo);
			$this->ajaxReturn($userinfo,'json');
		}
        else
		    $Return=M('userinfo')->add($userinfo);

        if($Return){
			$adduser->commit();
		}else{
			$adduser->rollback();
			$this->ajaxReturn(0,'json');
			die;
		}
		//发送激活通知
		$userinfo = M('userinfo')->where(array('uid'=>$uid))->find();
		$userinfo = class_convert($userinfo);
		$sex = $userinfo['sex']?'女':'男';
        $content = '注册成功，以下是您的基本信息：<br/>
        			姓名：'.$userinfo['name'].'<br/>
                    学号：'.$userinfo['number'].'<br/>
                    性别：'.$sex.'<br/>
                    入学时间：'.$userinfo['entrance'].'<br/>
                    学院：'.$userinfo['college'].'<br/>
                    专业：'.$userinfo['major'].'<br/>
                    班级：'.$userinfo['class'].'<br/>
                    QQ ：'.$userinfo['qq'].'(默认密码)<br/>
                    如果你不知道这是什么东西，说明该邮件为误发，请忽略。
                    ';
        if($Return > 0){
        	if(sendMail($email,'注册成功并激活-党建系统',$content))
				$Return = 'sended';
			else{
				$Return = 'sendfail';
			} 
        }
		


		$this->ajaxReturn($Return,'json');
		/*$url = U('Index/Index/index');
    	if($Return) $this->success("注册成功",$url);
    	else $this->error("注册失败，请重试或与管理员联系");*/
	}
    //密码找回
	public function forgetpasswd(){
		$code = I('code');
		if($code){
			$this->isreset = 1;
			$this->code = $code;
		}
		$this->show();
	}
	public function forgetHandle(){
		$type = I('type');
		
		if($type == 'find'){
			$email = I('email');
			$code = randCode();
			$url = U('Home/Login/sysredirect',array('code'=>$code),'','',true);
			$sendContent = '点击下面的链接重置密码<br/><a href="'.$url.'">'.$url.'</a><br>
							如果你不知道这是什么东西，说明该邮件为误发，请忽略。';
			if(M('user')->where(array('email'=>$email))->save(array('code'=>$code,'type'=>2))) //type=2 为找回密码
				if(sendMail(I('email'),'党建系统密码重置',$sendContent)){
					$this->ajaxReturn('sended','json');
				}else{
					$this->ajaxReturn('sendfail','json');
				}
			}else if($type == 'reset'){
				$p1 = I('p1');
				$p2 = I('p2');
				$code = I('code');
				if($p1==$p2&&$p1!=''){
					if(M('user')->where(array('code'=>$code,'type'=>2))->save(array('password'=>md5($p1)))){
						M('user')->where(array('code'=>$code))->save(array('code'=>'','type'=>''));
						$this->ajaxReturn('reseted','json');
					}else{
						$this->ajaxReturn('resetfail','json');
					}
				}else{
					$this->ajaxReturn('resetfail','json');
				}
			}
		
	
	}
    //public 
    //链接重定向函数
	public function sysredirect(){
		$code = I('code');
		//p($code);die;
		$type = M('user')->where(array('code'=>$code))->getField('type');
		//p($type);die;
		if($type == 1){
			redirect(U('Home/Login/register', array('code' => $code)), 0, '');
		}else if($type == 2){
			redirect(U('Home/Login/forgetpasswd', array('code' => $code)), 0, '');
		}else{
			$this->error('链接已经失效，请重新获取链接',U('Index/Index/index'));
		}

	}
}
?>