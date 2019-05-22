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
                        <div class="form-group">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                            <button type="button" class="btn btn-primary btn-sm _export"><i class="fa fa-download"></i> 导出数据</button>
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        {foreach from=$orderField key=field item=name}
                        <th{if $field eq 'date'} width="100"{/if} class="text-center">
                            {if !isset($disable_sort[$field])}<a href="{Service_Public::createOrderUri($formData.url,$search,$field)}">{$name} {if $search.order_field eq $field}<i class="fa fa-sort-{$search.order_type}"></i>{else}<i class="fa fa-sort"></i>{/if}</a>{else}{$name}{/if}
                        </th>
                        {/foreach}
                        {if !isset($orderField['cid'])}<th width="75">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr class="text-center">
                            <td>
                                {if $permission.reportdetail && !isset($orderField['cid'])}<a href="/data/reportDetail?begin_date={$item.date}&end_date={$item.date}">{$item.date}</a>{else}{$item.date}{/if}
                            </td>
                            {if isset($orderField['cid'])}<td>{$item.cid}</td>{/if}
                            {if isset($orderField['total_order'])}<td><a href="/order/default?begin_date={$item.date}&end_date={$item.date}" title="查看当日详细数据">{$item.total_order}</a></td>{/if}
                            {if isset($orderField['success_order'])}<td><a href="/order/default?begin_date={$item.date}&end_date={$item.date}&status=2" title="查看当日详细数据">{$item.success_order}</a></td>{/if}
                            {if isset($orderField['am_total'])}<td>{$item.am_total}</td>{/if}
                            {if isset($orderField['am_money'])}<td>{Service_Public::formatMoney($item.am_money)}</td>{/if}
                            {if isset($orderField['pm_total'])}<td>{$item.pm_total}</td>{/if}
                            {if isset($orderField['pm_money'])}<td>{Service_Public::formatMoney($item.pm_money)}</td>{/if}
                            {if isset($orderField['total_money'])}<td><a href="/order/default?begin_date={$item.date}&end_date={$item.date}&status=2" title="查看当日详细数据">{Service_Public::formatMoney($item.total_money)}</a></td>{/if}
                            {if !isset($orderField['cid'])}<td><a href="/data/reportDetail?begin_date={$item.date}&end_date={$item.date}" class="btn btn-primary-outline btn-xs"><i class=" wb-stats-bars"></i> 日详情报表</a></td>{/if}
                        </tr>
                    {/foreach}
                    <tr class="text-danger text-center">
                        <td>汇总</td>
                        {if isset($orderField['cid'])}<td></td>{/if}
                        {if isset($orderField['total_order'])}<td>{$total.total_order}</td>{/if}
                        {if isset($orderField['success_order'])}<td>{$total.success_order}</td>{/if}
                        {if isset($orderField['am_total'])}<td>{$total.am_total}</td>{/if}
                        {if isset($orderField['am_money'])}<td>{$total.am_money}</td>{/if}
                        {if isset($orderField['pm_total'])}<td>{$total.pm_total}</td>{/if}
                        {if isset($orderField['pm_money'])}<td>{$total.pm_money}</td>{/if}
                        {if isset($orderField['total_money'])}<td>{Service_Public::formatMoney($total.total_money)}</td>{/if}
                        {if !isset($orderField['cid'])}<td></td>{/if}
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