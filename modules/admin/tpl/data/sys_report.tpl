{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>系统支出数据报表</h5>
            </div>
            <div class="ibox-content clearfix">
                <form method="get" action="/data/sysreport" class="row m-b-md">
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="100" class="text-center">日期</th>
                        <th class="text-center">商务数</th>
                        <th class="text-center">组长数</th>
                        <th class="text-center">服务费用</th>
                        <th class="text-center">组长佣金</th>
                        <th class="text-center">收益</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr class="text-center">
                            <td>{$item.date}</td>
                            <td>{$item.media_total}</td>
                            <td>{$item.group_total}</td>
                            <td><a href="/data/sys?begin_date={$item.date}&end_date={$item.date}&type=1" title="查看当日详细数据">{Service_Public::formatMoney($item.money)}</a></td>
                            <td><a href="/data/sys?begin_date={$item.date}&end_date={$item.date}&type=2" title="查看当日详细数据">{Service_Public::formatMoney($item.group_money)}</a></td>
                            <td>{Service_Public::formatMoney($item.money-$item.group_money)}</td>
                        </tr>
                    {/foreach}
                    <tr class="text-danger text-center">
                        <td>汇总</td>
                        <td>{$total.media_total}</td>
                        <td>{$total.group_total}</td>
                        <td>{Service_Public::formatMoney($total.money)}</td>
                        <td>{Service_Public::formatMoney($total.group_money)}</td>
                        <td>{Service_Public::formatMoney($total.money-$total.group_money)}</td>
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
    });
</script>
{include 'footer.tpl'}