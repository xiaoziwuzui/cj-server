<?php
/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2019/5/22
 * Time: 14:57
 */
class Controller_Api_Auth extends FController {

    public function __construct()
    {
        parent::__construct();

    }

    public function pageAction($text = '很抱歉，您要访问的文件不存在！'){
        header("Content-type: text/html;charset=UTF-8");
        header("HTTP/1.1 404 Not Found");
        echo '<h1>'.$text.'</h1>';
        exit(0);
    }

    /**
     * 非法访问请求
     * @author 93307399@qq.com
     */
    public function failAction(){
        Service_Public::failLog();
        $this->pageAction('别这样');
    }

    public function showMessage($message, $msgType, $url = null)
    {
        $this->error($message);
    }

}
