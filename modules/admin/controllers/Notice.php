<?php

/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 14:57
 */
class Controller_Admin_Notice extends Controller_Admin_Abstract
{
    private $notice_type = array();

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if ($flag) {
            $this->notice_type = FConfig::get('type.notice_type');
            $this->assign('notice_type', $this->notice_type);
        }
        return $flag;
    }

    /**
     * 公告列表
     */
    public function defaultAction()
    {
        $type = FRequest::getInt('type');
        $page = FRequest::getInt('page');
        $uid  = FRequest::getInt('uid');
        $keyword  = FRequest::getString('keyword');
        $status    = FRequest::getInt('status');
        $startime  = strtotime(FRequest::getString('begin_date'));
        $endtime   = strtotime(FRequest::getString('end_date'));
        $where = array(
            'status' => array('lt' => 9),
        );
        if ($keyword) {
            if (preg_match('#^\d+$#', $keyword)) {
                $where['id'] = $keyword;
            } else {
                $where['title'] = array('like' => $keyword);
            }
        }
        $startime = $startime === false ? strtotime(date('Y-m-d',strtotime('-10 day')) . ' 00:00:00') : strtotime(date('Y-m-d',$startime) . ' 00:00:00');
        $endtime = $endtime === false ? strtotime(date('Y-m-d') . ' 23:59:59') : strtotime(date('Y-m-d',$endtime) . ' 23:59:59');
        $where['create_time'] = array(
            'gte' => $startime,
            'lte' => $endtime,
        );
        if($status > 0 && isset($this->status_type[$status])){
            $where['status'] = $status;
        }

        if($type > 0){
            $where['type'] = $type;
        }
        $permissionWhere = Service_Permission::getByPositionWhereV2();
        if($permissionWhere['where'] != false){
            $where['editor'] = $permissionWhere['where'];
        }else {
            if ($uid > 0) {
                $where['editor'] = $uid;
            }
        }
        $Table = new FTable('notice');
        $data = $Table->page($page)->where($where)->order(array('id' => 'desc'))->limit(30)->select();
        $pagerInfo = $Table->getPagerInfo();
        $this->assign('begin_date', date('Y-m-d',$startime));
        $this->assign('end_date', date('Y-m-d',$endtime));
        $this->assign('page_info', $pagerInfo);
        $this->assign('list', $data);
        $this->display('notice/list');
    }

    /**
     * 编辑公告信息
     */
    public function modifyAction()
    {
        global $_F;
        $id    = FRequest::getInt('id');
        $Table = new FTable('notice');
        $info  = array();

        if ($id) {
            $info = $Table->where(array('id' => $id))->find();
            if (!$info) {
                $id = 0;
            }
        }
        if ($this->isPost()) {
            $title   = trim(FRequest::getPostString('title'));
            $content = FRequest::getPostString('content');
            $publish_name = FRequest::getPostString('publish_name');
            $status  = FRequest::getPostInt('status');
            $type    = FRequest::getPostInt('type');

            if ($title == '') {
                $this->error('标题不能为空！');
            }
            $data = array(
                'title'   => $title,
                'content' => $content,
                'publish_name'  => $publish_name,
                'status'  => $status,
                'type'    => $type,
            );

            if ($id <= 0) {
                $data['editor'] = $_F['uid'];
                $data['hits']   = 0;
                $data['create_time'] = time();
                $result = $Table->insert($data);
                Service_Manager::addManagerLog($result);
            } else {
                $data['editor'] = $_F['uid'];
                $result = $Table->where(array('id' => $id))->update1($data);
                Service_Manager::addManagerLog($id);
            }
            if ($result) {
                $this->success(($id > 0 ? '修改' : '发布') . '公告成功！', '/notice/default');
            } else {
                $this->error('操作失败');
            }
        }

        if ($id > 0 && $info) {
            $this->assign('info', $info);
            $this->assign('content_editor', '<script id="content" name="content" type="text/plain" style="width:800px;height:400px;">'.$info['content'].'</script>');
        } else {
            $this->assign('info', array(
                'status' => 1,
                'type'   => 1
            ));
            $this->assign('content_editor', '<script id="content" name="content" type="text/plain" style="width:800px;height:400px;"></script>');
        }
        $this->assign('init_editor', Service_Public::createEditor(array(
            'toolbars' => array('source', 'undo', 'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify','redo','bold', 'italic', 'underline','insertimage', 'inserttable')
        )));

        $this->display('notice/modify');
    }

    /**
     * 删除公告
     */
    public function deleteAction()
    {
        $id = FRequest::getInt('id');
        if ($id) {
            $Table = new FTable('notice');
            $Table->where(array('id' => $id))->update1(array('status' => 9));
            Service_Manager::addManagerLog($id);
            $this->success('删除公告成功！', '/notice/default');
        } else {
            $this->error('error');
        }
    }

    /**
     * 查看公告
     * @author 93307399@qq.com
     */
    public function viewAction(){
        $id = FRequest::getInt('id');
        if(!$id){
            $this->error('没有要查看的公告!');
        }
        $Table = new FTable('notice','n');
        $info = $Table->fields('n.*,m.truename')->leftJoin('manager','m','n.editor=m.uid')->where(array('n.id' => $id,'n.type'=>2))->find();
        if (!$info) {
            $this->error('没有要查看的公告!');
        }
        $Table = new FTable('notice');
        $Table->where(array('id'=>$id))->increase('hits',1);
        $this->assign('info', $info);
        $this->display('notice/review');
    }
}