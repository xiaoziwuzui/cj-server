{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>{$page_title}</h5>
            </div>
            <div class="ibox-content clearfix">
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th colspan="4" class="text-center">用户信息</th>
                        <th colspan="6" class="text-center">支出</th>
                        <th colspan="2" class="text-center">收入</th>
                        <th colspan="3" class="text-center">出稿</th>
                        <th colspan="2" class="text-center">收稿</th>
                        <th colspan="2" class="text-center">余额</th>
                    </tr>
                    <tr>
                        <th width="30">UID</th>
                        <th width="100">登录名</th>
                        <th width="100">手机号码</th>
                        <th class="text-left">用户姓名</th>
                        <th width="50" class="text-center">佣金</th>
                        <th width="50" class="text-center">奖金</th>
                        <th width="60" class="text-center">服务费</th>
                        <th width="50" class="text-center">冻结</th>
                        <th width="50" class="text-center">素材</th>
                        <th width="50" class="text-center">消费</th>

                        <th width="50" class="text-center">充值</th>
                        <th width="50" class="text-center">出稿</th>

                        <th width="50" class="text-center">稿数</th>
                        <th width="50" class="text-center">退款</th>
                        <th width="60" class="text-center">投诉率</th>

                        <th width="50" class="text-center">购买</th>
                        <th width="50" class="text-center">退款</th>

                        <th width="50" class="text-center">余额</th>
                        <th width="50" class="text-center">差额</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.uid}</td>
                            <td>{$item.username}</td>
                            <td>{$item.mobile}</td>
                            <td class="text-left">{$item.truename}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.zmoney)}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.bonus)}</td>
                            <td class="text-right text-info">{Service_Public::formatMoney($item.fmoney)}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.froze)}</td>
                            <td class="text-right text-danger">{Service_Public::formatMoney($item.down_money)}</td>
                            <td class="text-right text-danger">{Service_Public::formatMoney($item.zmoney+$item.bonus+$item.fmoney+$item.buy_money+$item.down_money)}</td>

                            <td class="text-right">{Service_Public::formatMoney($item.charge)}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.camoney)}</td>

                            <td class="text-right">{$item.fine_count+$item.order_count}</td>
                            <td class="text-right">{intval($item.fine_count)}</td>
                            <td class="text-right">{if $item.fine_count gt 0}{round($item.fine_count / ($item.fine_count + $item.order_count),2)}{else}0{/if}</td>

                            <td class="text-right">{Service_Public::formatMoney($item.buy_money)}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.refund_money)}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.money)}</td>
                            <td class="text-right text-warning">{Service_Public::formatMoney(($item.charge + $item.camoney - ($item.fine_money+$item.zmoney+$item.bonus+$item.fmoney+$item.buy_money+$item.froze+$item.money+$item.down_money)))}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                {include "pager.tpl"}
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}