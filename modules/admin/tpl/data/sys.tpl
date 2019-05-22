{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>系统收支流水</h5>
            </div>
            <div class="ibox-content clearfix">
                <form method="get" action="/data/sys" class="row m-b-md">
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">商务ID</span>
                                <input class="form-control" name="media_id" value="{$smarty.get.media_id}" placeholder="商务ID" size="9"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">用户ID</span>
                                <input class="form-control" name="uid" value="{$smarty.get.uid}" placeholder="用户ID" size="9"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">文章ID</span>
                                <input class="form-control" name="article_id" value="{$smarty.get.article_id}" placeholder="文章ID" size="9"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">流水类型</span>
                                <select name="type" class="form-control" title="流水类型">
                                    <option value="">--请选择--</option>
                                    {foreach from=$cash_type item=item key=key}
                                        <option value="{$key}"{if $smarty.get.type eq $key} selected="selected"{/if}>{$item}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="wb-search"></i> 搜 索</button>
                            <a href="{$formData.url}" class="btn btn-danger btn-sm"><i class="wb-close"></i> 清空条件</a>
                        </div>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="55" class="text-center">商务</th>
                        <th width="70" class="text-left">类型</th>
                        <th width="70" class="text-left">金额</th>
                        <th width="70" class="text-left">用户</th>
                        <th class="text-left">文章ID</th>
                        <th width="145" class="text-center">创建时间</th>
                        <th width="70" class="text-center">操作人</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td class="text-center">{$item.media_id}</td>
                            <td>{$cash_type[$item.type]}</td>
                            <td>{Service_Public::formatMoney($item.money)}</td>
                            <td>{$item.uid}</td>
                            <td>{$item.article_id}</td>
                            <td>{$item.create_time|date_format:'Y-m-d H:i:s'}</td>
                            <td class="text-center">{$item.editor}</td>
                        </tr>
                    {/foreach}
                    {if isset($total)}<tr class="text-danger">
                        <td class="text-center">汇总</td>
                        <td></td>
                        <td>{Service_Public::formatMoney($total.money)}</td>
                        <td colspan="4"></td>
                    </tr>{/if}
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