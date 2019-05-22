{include 'header.tpl'}
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
                                <span class="input-group-addon">用户ID</span>
                                <input class="form-control" name="uid" value="{$smarty.get.uid}" placeholder="用户ID" size="9"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">收款姓名</span>
                                <input class="form-control" name="pay_truename" value="{$smarty.get.pay_truename}" placeholder="收款姓名" size="12"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">付款状态</span>
                                <select name="status" class="form-control" title="付款状态">
                                    <option value="">--请选择--</option>
                                    {foreach from=$withdraw_status_type item=item key=key}
                                        <option value="{$key}"{if $status eq $key} selected="selected"{/if}>{$item}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">支付平台</span>
                                <select name="platform" class="form-control" title="支付平台">
                                    <option value="">--请选择--</option>
                                    {foreach from=$pay_type item=item key=key}
                                        <option value="{$key}"{if $smarty.get.platform eq $key} selected="selected"{/if}>{$item}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                            <button type="button" class="btn btn-primary-outline btn-sm _export"><i class="fa fa-download"></i> 导出数据</button>
                        </div>
                    </div>
                </form>
                <form method="post" action="/data/paywithdrawbatch" class="batchForm" data-rel="ajax">
                <table class="table table-striped table-bordered table-hover m-b-sm">
                    <thead>
                    <tr>
                        {if $permission.paywithdraw || $permission.passwithdraw}<th class="text-center" width="50"><input type="checkbox" class="_checkAll" title="全选"></th>{/if}
                        <th class="text-left">周期</th>
                        <th width="80" class="text-center">用户ID</th>
                        <th width="80">用户名</th>
                        <th width="75" class="text-right">金额</th>
                        <th width="110" class="text-left">收款信息</th>
                        <th width="180" class="text-center">时间</th>
                        <th width="100" class="text-center">备注</th>
                        <th width="60" class="text-center">状态</th>
                        {if $permission.control}<th width="80" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$withdraw item=item}
                        <tr>
                            {if $permission.control}<th class="text-center">{if $item.status eq 1}<input type="checkbox" class="_checkItem" value="{$item.id}" name="ids[]" title="">{/if}</th>{/if}
                            <td>{$item.startdate}-{$item.enddate}</td>
                            <td class="text-center">{$item.uid}</td>
                            <td>{$item.username}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.money)}</td>
                            <td class="text-left"><p>{$pay_type[$item.pay_type]}：{$item.pay_truename}</p><p>{$item.pay_account}</p></td>
                            <td class="text-left">
                                <p>提现:{$item.create_time|date_format:"Y-m-d H:i:s"}</p>
                                <p>{if $item.update_time gt 0}{if $item.status eq 2}付款{else}打回{/if}:{$item.update_time|date_format:"Y-m-d H:i:s"}{/if}</p>
                            </td>
                            <td class="text-center">{$item.remark}</td>
                            <td class="text-center" data-id="{$item.id}">{$withdraw_status_type[$item.status]}</td>
                            {if $permission.control}<td class="text-center">
                                {if $item.status eq 1}
                                    {if $permission.paywithdraw}<a data-rel="ajax" href="/data/paywithdraw?id={$item.id}" class="text-success" data-text="确定要付款吗？">付款</a>{/if}
                                    {if $permission.passwithdraw && $item.status eq 1}<a data-rel="ajax" href="/data/passwithdraw?id={$item.id}" data-text="请填写打回理由" data-prompt="remark" data-width="500" data-height="100" class="text-danger">打回</a>{/if}
                                {else}
                                    <p><span class="text-success">已处理</span></p>
                                    {if $permission.recover}<a data-rel="ajax" href="/data/recover?id={$item.id}" class="text-danger" data-text="确定要恢复吗？">重新恢复</a>{/if}
                                {/if}
                            </td>{/if}
                        </tr>
                    {/foreach}
                    <tr class="text-danger">
                        <td>汇总</td>
                        <td colspan="3"></td>
                        <td class="text-right">{Service_Public::formatMoney($total.money)}</td>
                        <td colspan="4"></td>
                        {if $permission.control}<td class="text-center"></td>{/if}
                    </tr>

                    </tbody>
                </table>
                {if $permission.control}
                    <div class="col-sm-12 form-inline p-l-none m-b-sm">
                        {if $permission.paywithdraw}<button type="button" class="btn btn-sm btn-primary-outline batchBtn" data-action="/data/paywithdrawbatch">批量付款</button>{/if}
                        {if $permission.passwithdraw}<button type="button" class="btn btn-sm btn-danger-outline batchBtn" data-action="/data/passwithdrawbatch">批量打回</button>{/if}
                        <input type="text" value="" size="30" name="remark" placeholder="打回时请填写拒绝原因" class="form-control">
                    </div>
                {/if}
                </form>
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
        $('button.batchBtn').click(function (e) {
            var self = $(this),action=self.data('action'),$form=$('.batchForm'),$checkBoxLength=$('._checkItem:checked').length;
            if(!action) return true;
            if($checkBoxLength === 0){
                unit.error('没有选中任何记录!');
                return true;
            }
            unit.confirm('确定要对这些记录进行批量操作吗?',function () {
                $form.attr('action',action);
                $form.submit();
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