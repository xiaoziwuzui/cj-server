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
                                <span class="input-group-addon">门店名称</span>
                                <input class="form-control" name="business" value="{$business}" size="12" placeholder="门店名称"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="账号状态">
                                <option value="">门店状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.status eq $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="wb-search"></i> 查询</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>

                        {if $permission.poimodify}
                        <div class="form-group form-group-sm"><a class="btn btn-primary-outline btn-sm" href="/merchants/poimodify" data-rel="tab" data-size="640,auto" data-title="添加商户门店"><i class="wb-plus"></i> 添加门店</a></div>
                        {/if}
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th width="30">ID</th>
                        <th width="88">所属商户</th>
                        <th width="100">门店名称</th>
                        <th width="100">门店电话</th>
                        <th>图集</th>
                        <th width="210" class="text-left">创建时间</th>
                        <th width="45">状态</th>
                        {if $permission.control}<th width="80" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.id}</td>
                            <td class="text-left">{$item.real_name}</td>
                            <td>{$item.business_name}</td>
                            <td>{$item.telephone}</td>
                            <td>{$item.telephone}</td>
                            <td>{$item.create_time|date_format:"Y-m-d H:i:s"}</td>
                            <td title="{$item.status}">{$status_type[$item.status]} </td>
                            {if $permission.control}<td class="text-left children_nowrap">
                                {if $permission.poimodify}<a data-rel="ajax" href="/merchants/poimodify?id={$item.id}" class="text-info" data-width="500" data-height="510" data-title="编辑门店资料">编辑</a>{/if}
                                {if $permission.poidelete}<a data-rel="ajax" href="/merchants/delete?id={$item.id}" data-text="确定要删除这个门店吗？" class="text-danger">删除</a>{/if}
                                {if $permission.cardmodify}<a data-rel="ajax" href="/card/modify?pid={$item.id}" class="text-primary">卡券</a>{/if}
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