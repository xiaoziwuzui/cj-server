{include 'header.tpl'}
<style type="text/css">
    .order-user{ }
    .order-user img{
        display: inline-block;
        vertical-align: middle;
        max-width: 26px;
        max-height: 26px;
    }
    .order-user p{
        display: inline-block;
        vertical-align: middle;
        white-space:nowrap;text-overflow: ellipsis;max-width:100px;overflow: hidden;
    }
    .order-user p>span{
        font-size: 12px;
        display: block;
        white-space:nowrap;text-overflow: ellipsis;max-width:100px;overflow: hidden;
    }
</style>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            {include 'page_title.tpl'}
            <div class="ibox-content clearfix">
                <form method="get" action="{$formData.url}" class="row m-b-md" id="_sForm">
                    <input type="hidden" name="export" value="0" />
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">订单号</span>
                                <input class="form-control" name="oid" value="{$smarty.get.oid}" placeholder="订单号" size="20"/>
                            </div>
                        </div>
{*                        <div class="form-group form-group-sm">*}
{*                            <div class="input-group">*}
{*                                <span class="input-group-addon">三方订单号</span>*}
{*                                <input class="form-control" name="trade_no" value="{$smarty.get.trade_no}" placeholder="三方订单号" size="20"/>*}
{*                            </div>*}
{*                        </div>*}
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">车牌号</span>
                                <input class="form-control" name="number" value="{$smarty.get.number}" placeholder="车牌号(模糊搜索)" size="15"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">用户UID</span>
                                <input class="form-control" name="uid" value="{$smarty.get.uid}" placeholder="用户uid" size="8"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="订单状态">
                                <option value="">订单状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $set_status === $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="platform" class="form-control _chosen" title="支付平台">
                                <option value="">支付平台</option>
                                {foreach from=$pay_type item=item key=key}
                                    <option value="{$key}"{if $set_platform === $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                            <button type="button" class="btn btn-primary btn-sm _export"><i class="fa fa-download"></i> 导出数据</button>
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="100">订单号</th>
                        <th>车牌</th>
{*                        <th width="100" class="text-center">三方订单号</th>*}
                        <th width="150" class="text-left">用户(头像/昵称/UID)</th>
                        <th width="75" class="text-right">金额</th>
                        <th width="75">支付平台</th>
                        <th width="145" class="text-center">下单时间</th>
                        <th width="145" class="text-center">付款时间</th>
                        <th width="70" class="text-center">状态</th>
                        {if $permission.control}<th width="80" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td><p title="{$item.plate_order}">{$item.order_no}</p></td>
                            <td>{$item.plate}</td>
{*                            <td>{$item.trade_order}</td>*}
                            <td class="text-left">
                                <div class="order-user" title="用户UID:{$item.uid}">
                                    <img src="{$item.avatar}" alt="" />
                                    <p>
                                        <span>{$item.nickname}</span>
                                        <span>{$item.uid}</span>
                                    </p>
                                </div>
                            </td>
                            <td class="text-right"><span class="text-info">{Service_Public::formatMoney($item.money)}</span></td>
                            <td class="text-center">{if $item.status eq 3}<span class="text-warning">手动确认</span>{else}{$pay_type[$item.pay_type]}{/if}</td>
                            <td>{$item.create_time|date_format:'Y-m-d H:i:s'}</td>
                            <td class="text-center">{if $item.pay_time gt 0}{$item.pay_time|date_format:'Y-m-d H:i:s'}{else}--{/if}</td>
                            <td>{$status_type[$item.status]}</td>
                            {if $permission.control}<td class="text-center">
                                {if $permission.compalete && $item.status eq 1}<a href="/order/compalete?id={$item.id}" data-rel="ajax" data-text="确定要手动确认这个支付订单吗?" class="text-danger">确认</a> {/if}
                                {if $permission.detail}<a href="/order/detail?id={$item.id}" data-rel="ajax" data-size="500,auto" class="text-info">详情</a> {/if}
                            </td>{/if}
                        </tr>
                    {/foreach}
                    <tr class="text-danger">
                        <td class="text-center">汇总</td>
                        <td colspan="2"></td>
                        <td class="text-right">{Service_Public::formatMoney($total.money)}</td>
                        <td colspan="4"></td>
                        {if $permission.control}<td></td>{/if}
                    </tr>
                    </tbody>
                </table>
                {include "pager.tpl"}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function (e) {
        layui.use('laydate',function () {
            layui.laydate.render({
                elem: '._startTime',
                max:'{date('Y-m-d')}'
            });
            layui.laydate.render({
                elem: '._endTime',
                max:'{date('Y-m-d')}'
            });
        });
        $('button._export').click(function (e) {
            var $input = $('input[name="export"]');
            $input.val(520);
            $('#_sForm').trigger('submit');
            $input.val(0);
        });
    });
</script>
{include 'footer.tpl'}