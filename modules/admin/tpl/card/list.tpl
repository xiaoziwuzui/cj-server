{include 'header.tpl'}
<style type="text/css">
    .onoffswitch-inner:before{
        content: "启用";
    }
    .onoffswitch-inner:after{
        content: "停用";
    }
</style>
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
                                <span class="input-group-addon">电子券名称</span>
                                <input class="form-control" name="title" value="{$smarty.get.title}" size="12" placeholder="电子券名称(模糊搜索)"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="电子券状态">
                                <option value="">电子券状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $smarty.get.status eq $key} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary-outline btn-sm"><i class="glyphicon glyphicon-search"></i> 查询</button>
                            <a href="{$formData.url}" class="btn btn-danger-outline btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>
                        {if $permission.modify}
                            <div class="form-group form-group-sm"><a class="btn btn-primary-outline btn-sm" href="/card/modify" data-rel="tab" data-size="500,auto" data-title="添加电子券"><i class="wb-plus"></i> 添加电子券</a></div>
                        {/if}
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th width="30">ID</th>
                        <th>电子券标题</th>
                        <th>内容介绍</th>
                        <th width="75" class="text-center">投放量</th>
                        <th width="75" class="text-center">已领取</th>
                        <th width="60" class="text-center">图片</th>
                        <th width="100" class="text-right">券面值</th>
                        <th width="155" class="text-center">上线时间</th>
                        <th width="45">状态</th>
                        {if $permission.control}<th width="80" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.id}</td>
                            <td>{$item.title}</td>
                            <td>{$item.intro}</td>
                            <td class="text-center">{if $item.total gt 0}{$item.total}{else}不限{/if}</td>
                            <td class="text-center">{$item.receive}</td>
                            <td class="text-center">{if $item.image eq ''}无图{else}<a href="{$item.image}" target="_blank" title="点击查看图片"><img src="{$item.image}" style="max-width: 40px;max-height: 40px;object-fit: cover;" /></a>{/if}</td>
                            <td class="text-right">{Service_Public::formatMoney($item.money)} 元</td>
                            <td class="text-center">{$item.create_time|date_format:"Y-m-d H:i:s"}</td>
                            <td>
                                {if $permission.switchcard}
                                    <div class="switch">
                                        <div class="onoffswitch">
                                            <input type="checkbox"{if $item.status eq 1} checked{/if} value="{$item.id}" class="onoffswitch-checkbox _setStatus" id="setStatus_{$item.id}">
                                            <label class="onoffswitch-label" for="setStatus_{$item.id}">
                                                <span class="onoffswitch-inner"></span> <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>
                                    </div>
                                {else}
                                    {$status_type[$item.status]}
                                {/if}
                            </td>
                            {if $permission.control}<td class="text-left children_nowrap">
                                {if $permission.modify}<a data-rel="tab" href="/card/modify?id={$item.id}" class="text-info" data-size="500,auto" data-title="编辑电子券资料">编辑</a>{/if}
                                {if $permission.delete}<a data-rel="ajax" href="/card/delete?id={$item.id}" data-text="确定要删除这个电子券吗？" class="text-danger">删除</a>{/if}
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
        {if $permission.switchcard}$('._setStatus').change(function (evt) {
            var $el = $(evt.currentTarget),val = parseInt($el.val()),check = $el.prop('checked');
            unit.api({
                url:'/card/switchcard',
                data: {
                    id: val
                },
                callback:function (result) {
                    if(result.status === 'ok'){
                        unit.msg('操作成功!');
                    }else{
                        unit.msg(result.msg);
                    }
                }
            });
        });{/if}
    });
</script>
{include 'footer.tpl'}