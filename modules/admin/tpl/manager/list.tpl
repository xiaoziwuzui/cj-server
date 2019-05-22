{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            {include 'page_title.tpl'}
            <div class="ibox-content clearfix">
                <form method="get" action="{$formData.url}" class="row m-b-sm">
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">搜索关键字</span>
                                <input class="form-control" name="keyword" value="{$keyword}" placeholder="用户ID,登录名"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="账号状态">
                                <option value="">账号状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.status eq $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="account_type" class="form-control _chosen" title="账号类型">
                                <option value="">账号类型</option>
                                {foreach from=$account_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.account_type eq $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="position_id" title="所属职位" class="form-control _chosen">
                                <option value="">所属职位</option>
                                {$position_select}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 查询</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>
                        {if $permission.modify}
                        <div class="form-group form-group-sm">
                            <a href="/manager/modify" class="btn btn-primary-outline btn-sm" data-rel="ajax" data-width="500" data-height="480"><i class="wb-plus"></i> 添加账号</a>
                        </div>
                        {/if}
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th width="30">UID</th>
                        <th width="100">登录名</th>
                        <th width="100">手机号码</th>
                        <th width="100">QQ号码</th>
                        <th class="text-left">称呼</th>
                        <th class="text-left" width="95">真实姓名</th>
                        <th width="100" class="text-center">账号类型</th>
                        <th width="100" class="text-center">职位</th>
                        <th width="145" class="text-center">最后登录</th>
                        <th width="45">状态</th>
                        {if $permission.control}<th width="80" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$managerList item=item}
                        <tr>
                            <td>{$item.uid}</td>
                            {if $permission.userauth && $item.uid neq $_F['uid'] && $item.status eq 1}
                                <td><a href="/manager/userauth?hash={$item.hash}" title="登录到用户前台" style="padding: 0;" target="_blank">{$item.username}</a></td>
                            {else}
                                <td>{$item.username}</td>
                            {/if}
                            <td>{$item.mobile}</td>
                            <td>{$item.qq}</td>
                            <td class="text-left">{$item.truename}</td>
                            <td class="text-left">{$item.real_name}</td>
                            <td class="text-center">{$account_type[$item.account_type]}</td>
                            <td class="text-center">{$item.position_name}</td>
                            <td>{if $item.last_login_time gt 0}{$item.last_login_time|date_format:"Y-m-d H:i:s"}{else}--{/if} </td>
                            <td>{$status_type[$item.status]} </td>
                            {if $permission.control}<td class="text-left children_nowrap">
                                {if $permission.modify}<a data-rel="ajax" href="/manager/modify?id={$item.uid}" class="text-success" data-size="500,auto">编辑</a>{/if}
                                {if $permission.delete}<a data-rel="ajax" href="/manager/delete?id={$item.uid}" data-text="确定要删除这个用户吗？" class="text-danger">删除</a>{/if}
                                {if $permission.managerpermission}<a data-rel="ajax" href="/manager/managerpermission?id={$item.uid}" class="text-success">权限</a>{/if}
                                {if $permission.getsuperpwd}<a data-rel="ajax" href="/manager/getsuperpwd?uid={$item.uid}" data-width="320" class="text-success">密码</a>{/if}</td>{/if}
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