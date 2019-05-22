<?php
/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 11:31
 */
$_config['type'] = array(
    /**
     * 状态
     */
    'status_type' => array(
        1 => '<span class="text-info">正常</span>',
        2 => '<span class="text-danger">停用</span>',
    ),
    /**
     * 账号状态类型
     */
    'account_status_type' => array(
        1 => '正常',
        2 => '停用',
    ),
    /**
     * 会员账号状态类型
     */
    'member_status_type' => array(
        1 => '<span class="text-info">正常</span>',
        2 => '<span class="text-danger">取关</span>',
    ),
    /**
     * 微信账号性别
     */
    'member_sex' => array(
        0 => '<span class="text-warning">未知</span>',
        1 => '<span class="text-info">男</span>',
        2 => '<span class="text-primary">女</span>',
    ),
    /**
     * 后台账号类型
     */
    'account_type_map' => array(
        1 => '普通',
        2 => '管理员',
    ),
    /**
     * 后台操作日志IP搜索方式
     */
    'ip_search_type' => array(
        1 => '前两段',
        2 => '前三段',
        3 => '全部匹配'
    ),
    /**
     * 数据排序类型
     */
    'order_type' => array(
        'asc' => '升序',
        'desc' => '降序',
    ),
    /**
     * 会员账号类型
     */
    'member_account_type' => array(
        1 => '渠道',
    ),
    /**
     * 会员支付账号类型
     */
    'member_pay_type' => array(
        1 => '微信',
    ),
    /**
     * 充值订单状态
     */
    'charge_status_type' => array(
        1 => '<span class="text-info">创建订单</span>',
        2 => '<span class="text-info">支付成功</span>',
        3 => '<span class="text-success">手动成功</span>',
        4 => '<span class="text-danger">支付失败</span>',
        5 => '<span class="text-danger">退款</span>',
    ),
    /**
     * 公告状态
     */
    'notice_type' => array(
        1 => '<span class="text-muted">用户公告</span>',
        2 => '<span class="text-danger">管理员公告</span>',
    ),
);