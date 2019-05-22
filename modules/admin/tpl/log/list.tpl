 {include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            {include 'page_title.tpl'}
            <div class="ibox-content clearfix">
                <form method="get" action="{$formData.url}" class="row m-b-md">
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">管理员ID</span>
                                <input class="form-control" name="mid" value="{$smarty.get.mid}" placeholder="管理员ID" size="9"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">操作标识</span>
                                <input class="form-control" name="s_action" value="{$s_action}" placeholder="操作标识" size="18"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">描述搜索</span>
                                <input class="form-control" name="s_intro" value="{$s_intro}" placeholder="描述搜索" size="15"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">IP</span>
                                <input class="form-control" name="s_ip" value="{$s_ip}" placeholder="客户端IP" size="10"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="s_ip_type" class="form-control _chosen" title="匹配类型">
                                <option value="">匹配类型</option>
                                {foreach from=$ip_search_type item=item key=k}
                                    <option value="{$k}" {if $s_ip_type eq $k}selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="100">操作人</th>
                        <th>操作标识</th>
                        <th class="text-left">事件描述</th>
                        <th width="100">操作IP</th>
                        <th width="145">操作时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.manager}[{$item.mid}]</td>
                            <td>{$item.action}</td>
                            <td class="text-left">{$item.comment}</td>
                            <td><a href="/Log/list?s_ip={$item.ip}">{$item.ip}</a></td>
                            <td title="{$item.id}">{$item.create_time}</td>
                        </tr>
                    {/foreach}
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