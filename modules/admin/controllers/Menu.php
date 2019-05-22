<?php

/**
 * Created by PhpStorm.
 * User: 93307399@qq.com
 * Date: 2018/05/10
 * Time: 11:14
 * 微信菜单管理
 */
class Controller_Admin_Menu extends Controller_Admin_Abstract
{
    /**
     * @var array
     */
    private $type_map = array(
        'click' => '点击按钮',
        'view'  => '跳转链接'
    );

    public function beforeAction()
    {
        $flag = parent::beforeAction();
        if ($flag) {
            $this->assign('type_map', $this->type_map);
        }
        return $flag;
    }

    /**
     * 菜单列表
     * @author 93307399@qq.com
     */
    public function defaultAction()
    {
        $Table = new FTable('wx_menu');
        $data  = $Table->order(array('list_order' => 'asc','id' => 'desc'))->select();
        $tree = new Service_Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $tree->pid = 'parent_id';
        $categorys = array();

        if (!empty($data)) {
            foreach ($data as $r) {
                $r['typename'] = $this->type_map[$r['type']];
                $r['content'] = $r['type'] == 'click' ? '点击按钮,推送关键字:'.$r['key'] : '跳转到:'.$r['url'];
                $r['str_manage'] = '';
                $r['str_manage'] .= '<a href="/menu/modify?parent_id='.$r['id'].'" data-rel="ajax" class="text-primary"><i class="fa fa-plus"></i> 添加子栏目</a>';
                $r['str_manage'] .= '<a href="/menu/modify?id='.$r['id'].'" class="text-success" data-rel="ajax"><i class="fa fa-edit"></i> 编辑</a>';
                $r['str_manage'] .= '<a href="/menu/delete?id='.$r['id'].'" class="text-danger" data-rel="ajax" data-text="确定要删除这个菜单吗?"><i class="fa fa-trash"></i> 删除</a>';
                $categorys[$r['id']] = $r;
            }
        }
        $str = "<tr>
					<td class=center><input name='order[\$id]' type='text' size='3' value='\$list_order' class='form-control center table-input'></td>
					<td >\$spacer\$name</td>
					<td class=left>\$content</td>
					<td class=center>\$typename</td>
					<td>\$str_manage</td>
				</tr>";
        $tree->init($categorys);
        $categorys = $tree->get_tree(0, $str);
        $this->assign('categorys', $categorys);
        $this->display('menu/list');
    }

    /**
     * 编辑菜单
     * @author 93307399@qq.com]
     */
    public function modifyAction(){
        $id    = FRequest::getInt('id');
        $Table = new FTable('wx_menu');
        $info  = array();

        if ($id) {
            $info = $Table->where(array('id' => $id))->find();
            if (!$info) {
                $id = 0;
            }
        }
        if ($this->isPost()) {
            $name  = trim(FRequest::getPostString('name'));
            $type  = FRequest::getPostString('type');
            $key   = FRequest::getPostString('key');
            $url   = FRequest::getPostString('url');
            $parent_id = FRequest::getPostInt('parent_id');
            $order     = FRequest::getPostInt('list_order');
            if (trim($name) == '') {
                $this->error('名称不能为空！');
            }
            if($type == 'click' && $key == ''){
                $this->error('菜单 KEY 值不能为空');
            }
            if($type == 'view' && $url == ''){
                $this->error('跳转链接不能为空');
            }
            $data = array(
                'name'       => $name,
                'parent_id'  => $parent_id,
                'list_order' => $order,
                'type'       => $type,
                'key'        => $key,
                'url'        => $url,
            );

            if ($id <= 0) {
                $result = $Table->insert($data);
                Service_Manager::addManagerLog($result);
            } else {
                $result = $Table->where(array('id' => $id))->update1($data);
                Service_Manager::addManagerLog($id);
            }
            if ($result) {
                $this->success(($id > 0 ? '修改' : '添加') . '菜单成功！', '/menu/default');
            } else {
                $this->error('操作失败');
            }
        }
        /**
         * 取一级菜单
         */
        $parent = $Table->where(array('parent_id'=>0))->order(array('list_order'=>'asc','id'=>'desc'))->select();
        $this->assign('parent', $parent);
        $parent_id = FRequest::getInt('parent_id');
        if ($info) {
            $this->assign('info', $info);
        } else {
            $this->assign('info', array(
                'parent_id'  => intval($parent_id),
                'name'       => '',
                'list_order' => 1,
                'type'       => 'click',
                'url'        => '',
                'key'        => '',
            ));
        }
        $this->display('menu/modify');
    }

    /**
     * 发布菜单
     * @author 93307399@qq.com
     */
    public function publishAction(){
        $options = FConfig::get('pay.gtxc');
        require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
        $weixin = new Wechat($options);
        $weixin->checkAuth();
        $Table = new FTable('wx_menu');
        $data  = $Table->order(array('list_order' => 'asc','id' => 'desc'))->select();
        $button = array();
        $list = array();
        foreach ($data as $k=>$v){
            if(isset($list[$v['id']])){
                $list[$v['id']] = array_merge($v,$list[$v['id']]);
            }else{
                if($v['parent_id'] > 0){
                    $list[$v['parent_id']]['menu'][] = $v;
                }else{
                    $list[$v['id']] = $v;
                }
            }
        }
        foreach ($list as $k=>$v){
            $item = array(
                'name' => $v['name'],
            );
            if(!isset($v['menu'])){
                if($v['type'] == 'view'){
                    $item['url'] = $v['url'];
                }
                if($v['type'] == 'click'){
                    $item['key'] = $v['key'];
                }
                $item['type'] = $v['type'];
            }else{
                $item['sub_button'] = array();
                foreach ($v['menu'] as $vv){
                    $citem = array(
                        'name' => $vv['name'],
                        'type' => $vv['type']
                    );
                    if($vv['type'] == 'view'){
                        $citem['url'] = $vv['url'];
                    }
                    if($vv['type'] == 'click'){
                        $citem['key'] = $vv['key'];
                    }
                    $item['sub_button'][] = $citem;
                }
            }
            $button[] = $item;
        }
        $data = array('button'=>$button);
        $result = $weixin->createMenu($data);
        $data = json_decode($result,true);
        if($data['errcode'] == 0){
            $this->success('发布菜单成功!');
        }else{
            $this->error('菜单发布失败');
        }
    }

    /**
     * 查询菜单
     * @author 93307399@qq.com
     */
    public function getMenuAction(){
        $options = FConfig::get('pay.gtxc');
        require_once(APP_ROOT . 'lib/weixin/Wechat.class.php');
        $weixin = new Wechat($options);
        $weixin->checkAuth();
        $result = $weixin->getMenu();
        dump($result);
    }

    /**
     * 更新排序
     * @author 93307399@qq.com
     */
    public function orderAction(){
        if($this->isPost()){
            $order = FRequest::getPostString('order');
            if(is_array($order)){
                $Table = new FTable('wx_menu');
                $success = 0;
                foreach ($order as $k=>$v){
                    $result = $Table->where(array('id'=>$k))->update1(array('list_order'=>intval($v)));
                    if($result){
                        $success = $success + 1;
                    }
                }
                $this->success('提交'.count($order).'项,其中'.$success.'项排序更新成功!','r');
            }
        }
        $this->error('设置失败!');
    }

    /**
     * 删除菜单
     * @author 93307399@qq.com
     */
    public function deleteAction(){
        $id = FRequest::getInt('id');
        if ($id) {
            $Table = new FTable('wx_menu');
            $Table->where(array('id' => $id))->remove(true);
            Service_Manager::addManagerLog($id);
            $this->success('删除菜单成功,请注意重新发布！', '/menu/default');
        } else {
            $this->error('error');
        }
    }

}