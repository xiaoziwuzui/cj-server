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
                                <span class="input-group-addon">商户ID</span>
                                <input class="form-control" name="uid" value="{$smarty.get.uid}" size="8" placeholder="商户ID"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">登录账号</span>
                                <input class="form-control" name="username" value="{$username}" size="12" placeholder="登录名"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">商户称呼</span>
                                <input class="form-control" name="truename" value="{$truename}" size="12" placeholder="商户称呼"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="type" class="form-control _chosen" title="账号类型">
                                <option value="">账号类型</option>
                                {foreach from=$merchants_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.type eq $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
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
                            <select name="order" class="form-control _chosen" title="排序方式">
                                <option value="">排序方式</option>
                                {foreach from=$order_name item=item key=key}
                                    <option value="{$key}"{if $order eq $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 查询</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>

                        {if $permission.modify}
                        <div class="form-group form-group-sm"><a class="btn btn-primary-outline btn-sm" href="/merchants/modify" data-rel="ajax" data-width="500" data-height="510" data-title="添加商户账号"><i class="wb-plus"></i> 添加账号</a></div>
                        {/if}
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th width="30">UID</th>
                        <th width="100">账号</th>
                        <th width="100">商户称呼</th>
                        <th width="100">手机号码</th>
                        <th>电子邮箱</th>
                        <th width="70" class="text-center">账号类型</th>
                        <th width="210" class="text-left">时间信息</th>
                        <th width="45">状态</th>
                        {if $permission.control}<th width="80" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.uid}</td>
                            {if $permission.userauth}
                                <td><a href="/merchants/userauth?hash={$item.hash}" title="登录到商户前台" style="padding: 0;" target="_blank">{$item.username}</a></td>
                            {else}
                                <td>{$item.username}</td>
                            {/if}
                            <td class="text-left">
                                <p>{$item.truename}</p>
                                <p>{$item.real_name}</p>
                            </td>
                            <td>{$item.mobile}</td>
                            <td>{$item.email}</td>
                            <td class="text-center"><p>{$merchants_type[$item.type]}</p></td>
                            <td style="font-size: 12px;">
                                <p>注册：{$item.create_time|date_format:"Y-m-d H:i:s"}</p>
                                <p>登录：{$item.last_login_time|date_format:"Y-m-d H:i:s"}</p>
                                <p>次数：{$item.login_hit}{if $item.last_login_ip neq ''}<span class="m-l-sm">最后IP：{$item.last_login_ip}</span>{/if}</p>
                            </td>
                            <td title="{$item.status}">{$status_type[$item.status]} </td>
                            {if $permission.control}<td class="text-left children_nowrap">
                                {if $permission.modify}<a data-rel="ajax" href="/merchants/modify?id={$item.uid}" class="text-info" data-width="500" data-height="510" data-title="编辑商户资料">编辑</a>{/if}
                                {*{if $permission.poi}<a data-rel="tab" href="/merchants/poi?uid={$item.uid}" class="text-info" data-title="{$item.truename}-门店管理">门店</a>{/if}*}
                                {if $permission.forbid && $item.status eq 1}<a data-rel="ajax" href="/merchants/forbid?id={$item.uid}" class="text-warning" data-size="500,auto"  data-title="填写封号原因">封号</a>{/if}
                                {if $permission.unforbid && $item.status eq 2}<a data-rel="ajax" href="/merchants/unforbid?id={$item.uid}" class="text-success" data-text="{if $item.remark}由于:{$item.remark} 被封号,{/if}确定要解封这个商户吗？">解封</a>{/if}
                                {if $permission.delete}<a data-rel="ajax" href="/merchants/delete?id={$item.uid}" data-text="确定要删除这个商户吗？" class="text-danger">删除</a>{/if}
                                {if $permission.pass && $item.status eq 3}<a data-rel="ajax" href="/merchants/pass?id={$item.uid}" data-text="确定要通过商户注册吗？" class="text-warning">审核</a>{/if}
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