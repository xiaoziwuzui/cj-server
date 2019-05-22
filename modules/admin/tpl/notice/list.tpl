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
                                <span class="input-group-addon">关键字</span>
                                <input class="form-control" name="keyword" value="{$smarty.get.keyword}" placeholder="ID,公告标题"/>
                            </div>
                        </div>
                        {if $permission.super}<div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">发布人</span>
                                <input class="form-control" name="uid" value="{$smarty.get.uid}" placeholder="发布人" size="8"/>
                            </div>
                            </div>{/if}
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">开始时间</span>
                                <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="10" value="{$begin_date}" placeholder="开始时间"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">结束时间</span>
                                <input type="text" class="form-control _endTime" readonly autocomplete="off" size="10" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="type" class="form-control _chosen" title="公告类型">
                                <option value="">公告类型</option>
                                {foreach from=$notice_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.type eq $key} selected="selected"{/if}>{strip_tags($item)}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="公告状态">
                                <option value="">公告状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.status eq $key} selected="selected"{/if}>{strip_tags($item)}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>
                        {if $permission.modify}
                            <div class="form-group form-group-sm"><a type="button" class="btn btn-primary-outline btn-sm" href="/notice/modify"><i class="wb-plus"></i> 发布公告</a></div>
                        {/if}
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="30">ID</th>
                        <th>公告标题</th>
                        <th width="60">查看数</th>
                        <th width="100">发布人姓名</th>
                        <th width="140">发布时间</th>
                        <th width="45">状态</th>
                        {if $permission.modify || $permission.delete}<th width="75" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.id}</td>
                            <td>{$item.title}</td>
                            <td class="text-center">{$item.hits}</td>
                            <td class="text-center">{$item.publish_name}</td>
                            <td class="text-center">{$item.create_time|date_format:"Y-m-d H:i:s"}</td>
                            <td title="{$item.status}">{$status_type[$item.status]} </td>
                            {if $permission.modify || $permission.delete}<td class="text-left">
                                {if $permission.modify}<a href="/notice/modify?id={$item.id}" class="text-success">编辑</a>{/if}
                                {if $permission.delete}<a data-rel="ajax" href="javascript:void(0);" data-href="/notice/delete?id={$item.id}" data-text="确定要删除这个公告吗？" class="btn-delete">删除</a>{/if}
                                </td>{/if}
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