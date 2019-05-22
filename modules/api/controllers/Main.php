<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2019/5/22
 * Time: 14:22
 */
class Controller_Api_Main extends Controller_Api_Abstract
{

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if($flag){
            //初始化一些东西
        }
        return $flag;
    }

    public function indexAction(){
        $this->success('success');
    }

    /**
     * 上传表单ID
     * @author 93307399@qq.com
     */
    public function UpFormIDAction(){

    }

    /**
     * 获取首页数据
     * @author 93307399@qq.com
     */
    public function getIndexAction(){

    }

    /**
     * 获取用户信息
     * @author 93307399@qq.com
     */
    public function getUserInfoAction(){

    }

    /**
     * 用户登录获取令牌
     * @author 93307399@qq.com
     */
    public function loginAction(){
        $code = FRequest::getString('code');
        if(strlen($code) == 0){
            $this->error('code empty!');
        }
        $w = new Service_Mini('cj');
        $result = $w->codeToSession($code);
        $this->output($result);
    }

}
