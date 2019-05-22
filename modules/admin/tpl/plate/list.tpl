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
                                <span class="input-group-addon">车牌</span>
                                <input class="form-control" name="keyword" value="{$smarty.get.keyword}" placeholder="车牌(模糊搜索)"/>
                            </div>
                        </div>
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
                            <select name="level" class="form-control _chosen" title="车辆类别">
                                <option value="">车辆类别</option>
                                {foreach from=$plate_level item=item key=key}
                                    <option value="{$key}"{if $level === $key} selected="selected"{/if}>{strip_tags($item)}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="车辆状态">
                                <option value="">车辆状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.status eq $key} selected="selected"{/if}>{strip_tags($item)}</option>
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
                        <th width="30">ID</th>
                        <th>车牌</th>
                        <th width="60">车头照</th>
                        <th width="85" class="text-center">分级</th>
                        <th width="140">最早入场</th>
                        <th width="45">状态</th>
                        {if $permission.control}<th width="75" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.id}</td>
                            <td>{$item.plate}</td>
                            <td class="text-center">{if $item.image eq ''}无图{else}<img src="{$item.image}" style="max-width: 55px;max-height: 55px;object-fit: cover;" />{/if}</td>
                            <td class="text-center" title="{$item.level}">{$plate_level[$item.level]} </td>
                            <td class="text-center">{$item.create_time|date_format:"Y-m-d H:i:s"}</td>
                            <td title="{$item.status}">{$status_type[$item.status]} </td>
                            {if $permission.control}<td class="text-left">
                                {if $permission.detail}<a data-rel="ajax" data-size="640,auto" href="/plate/detail?id={$item.id}" class="text-success">详情</a>{/if}
                                {if $permission.level}<a data-rel="ajax" data-size="640,auto" href="/plate/level?id={$item.id}" class="text-success">分级</a>{/if}
                                </td>
                            {/if}
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