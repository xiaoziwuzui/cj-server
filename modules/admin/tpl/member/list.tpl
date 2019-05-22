{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>用户列表</h5>
            </div>
            <div class="ibox-content clearfix">
                <form method="get" action="{$formData.url}" class="row m-b-sm">
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">关键字</span>
                                <input class="form-control" name="keyword" value="{$keyword}" placeholder="用户ID,昵称" size="15"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">姓名</span>
                                <input class="form-control" name="truename" value="{$truename}" placeholder="用户姓名" size="15"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="sex" class="form-control _chosen" title="用户性别" style="border-radius: 0;">
                                <option value="">用户性别</option>
                                {foreach from=$member_sex item=item key=key}
                                    <option value="{$key}" {if $sex === $key}selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="账号状态" style="border-radius: 0;">
                                <option value="">账号状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}" {if $smarty.get.status eq $key}selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary btn-sm btn-primary-outline"><i class="wb-search"></i> 查询</button>
                            <a href="{$formData.url}" class="btn btn-danger btn-sm btn-danger-outline"><i class="wb-close"></i> 清空条件</a>
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th width="30">UID</th>
                        <th width="45">头像</th>
                        <th>昵称</th>
                        <th width="100">手机号码</th>
                        {*<th width="100">姓名</th>*}
                        {*<th width="100">地理信息</th>*}
                        <th width="50">性别</th>
                        <th width="140" class="text-center">关注时间</th>
                        <th width="140" class="text-center">最后活跃</th>
                        <th width="45">状态</th>
                        {if $permission.control}<th width="105" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.uid}</td>
                            <td class="text-center">{if $item.avatar eq ''}无图{else}<img src="{$item.avatar}" style="max-width: 33px;max-height: 33px;object-fit: cover;" />{/if}</td>
                            <td>{$item.nickname}</td>
                            <td>{$item.mobile}</td>
                            {*<td class="text-left">{$item.truename}</td>*}
                            {*<td class="text-left">{$item.area_info}</td>*}
                            <td title="{$item.sex}">{$member_sex[$item.sex]} </td>
                            <td>{$item.register_time|date_format:"Y-m-d H:i:s"} </td>
                            <td>{$item.last_time|date_format:"Y-m-d H:i:s"} </td>
                            <td title="{$item.status}">{$status_type[$item.status]} </td>
                            {if $permission.control}<td class="text-left children_nowrap">
                                {if $permission.modify}<a data-rel="ajax" href="/member/modify?id={$item.uid}" class="text-success" data-size="550,auto">编辑</a>{/if}
                                {if $permission.referuser}<a data-rel="ajax" href="/member/referuser?id={$item.uid}" data-text="确定要重新获取这个用户的资料吗？" class="btn-delete">资料</a>{/if}
                                {if $permission.delete}<a data-rel="ajax" href="/member/delete?id={$item.uid}" data-text="确定要删除这个用户吗？删除后无法恢复现有资料!" class="btn-delete">删除</a>{/if}
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
{include 'footer.tpl'}