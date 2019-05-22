{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>{$page_title}</h5>
            </div>
            <div class="ibox-content clearfix">
                <form method="get" action="{$formData.url}" class="row m-b-md">
                    <div class="col-sm-12 form-inline">
                        <div class="form-group form-group-sm">
                            <div class="input-group">
                                <span class="input-group-addon">用户手机</span>
                                <input class="form-control" name="mobile" value="{$smarty.get.mobile}" placeholder="用户手机" size="15"/>
                            </div>
                        </div>
                        <div class="form-group form-group-sm">
                            <label>时间范围</label>
                            <input type="text" class="form-control _startTime" name="begin_date" size="11" value="{$begin_date}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" size="11" name="end_date" value="{$end_date}" placeholder="结束时间"/>
                        </div>
                        <div class="form-group form-group-sm">
                            <select name="status" class="form-control _chosen" title="发送状态">
                                <option value="">发送状态</option>
                                {foreach from=$status_type item=item key=key}
                                    <option value="{$key}"{if $set_status === $key} selected="selected"{/if}>{$item}</option>
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
                        <th width="100">手机号码</th>
                        <th>短信内容</th>
                        <th width="145" class="text-center">发送时间</th>
                        <th width="70" class="text-center">状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$list item=item}
                        <tr>
                            <td>{$item.mobile}</td>
                            <td>{$item.msg}</td>
                            <td>{$item.create_time|date_format:'Y-m-d H:i:s'}</td>
                            <td>{$status_type[$item.code]}</td>
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