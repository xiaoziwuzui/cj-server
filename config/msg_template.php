<?php
/**
 * Created by PhpStorm.
 * User: jiangtaiping
 * Date: 2017/2/23
 * Time: 11:31
 */
$_config['msg_template'] = array(
    /**
     * 测试(抽奖成功通知)
     * lLbESu1d8emXHuQdTYzUSeh-jgN-lMup5WKkN8yQLZE
     * {{first.DATA}} 奖品：{{keyword1.DATA}} 抽奖时间：{{keyword2.DATA}} {{remark.DATA}}
     */
    'push_fee'   => array(
        'template_id' => 'lLbESu1d8emXHuQdTYzUSeh-jgN-lMup5WKkN8yQLZE',
        'data'        => array(
            'first'    => array(
                'tpl' => '尊敬的用户',
                'color' => '#173177',
            ),
            'keyword1' => array(
                'value' => 'title',
                'color' => '#173177',
            ),
            'keyword2' => 'create_time',
            'remark'   => array(
                'tpl' => 'remark',
                'color' => '#173177',
            ),
        ),
        //'url' => 'member/success',
    ),
    /**
     * 物品名称 {{keyword1.DATA}} 购买时间 {{keyword2.DATA}} 交易单号 {{keyword3.DATA}} 购买价格 {{keyword4.DATA}} 数量 {{keyword5.DATA}} 备注 {{keyword6.DATA}}
     */
    'buy_success' => array(
        'template_id' => '5hQt5SPJdEU5OkdoBpyMfEWV2UDNrbyziZmxbCJGPiI',
        'data' => array(
            'keyword1' => 'title',
            'keyword2' => 'create_time',
            'keyword3' => 'order_no',
            'keyword4' => 'price',
            'keyword5' => 'number',
            'keyword6' => 'remark',
        ),
        //'url' => 'member/success',
    ),
);