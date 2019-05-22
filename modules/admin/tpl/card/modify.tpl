{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            {include 'page_title.tpl'}
            <div class="ibox-content clearfix">
            <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal">
            <div class="form-group col-sm-12">
                <table width="auto">
                    <tbody>
                    <tr>
                        <td>
                            <div class="col-sm-12 form-group-sm p-l-none">
                                <label>电子券名称：</label>
                                <div>
                                    <select name="mid" class="form-control _chosen" title="所属商户">
                                        <option value="">所属商户</option>
                                        {foreach from=$merchants_list item=item key=key}
                                            <option value="{$key}"{if $info.mid eq $key} selected="selected"{/if}>{$item.real_name}</option>
                                        {/foreach}
                                    </select>
                                    <input name="title" value="{$info.title}" size="30" class="form-control" placeholder="显示标题"/>
                                    <div><span class="help-block m-t-xs"><i class="wb-info-circle"></i> 20个汉字或40个英文字符内</span></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-group col-sm-12">
                <label>内容介绍：</label>
                <div>
                    <textarea class="form-control" name="intro" placeholder="券内容介绍" cols="60" rows="3">{$info.intro}</textarea>
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label>使用地点：</label>
                <div>
                    <textarea class="form-control" name="use_address" placeholder="使用地点" cols="60" rows="3">{$info.use_address}</textarea>
                </div>
            </div>
            {$thumbUpload}
            {$imageUpload}
            <div class="form-group col-sm-12">
                <table width="auto">
                    <tbody>
                    <tr>
                        <td>
                            <div class="col-sm-12 form-group-sm p-l-none">
                                <label>面值额度：</label>
                                <div>
                                    <div class="input-group">
                                        <input name="money" value="{$info.money}" class="form-control" size="12" placeholder="面值"/>
                                        <span class="input-group-addon">元</span>
                                    </div>
                                    <div><span class="help-block m-t-xs"><i class="wb-info-circle"></i> 面值,可以有小数</span></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="col-sm-12 form-group-sm p-l-none">
                                <label>投放数量：</label>
                                <div>
                                    <input name="total" value="{$info.total}"{if $info.id gt 0} readonly{/if} class="form-control" size="15" placeholder="数量"/>
                                    <div><span class="help-block m-t-xs"><i class="wb-info-circle"></i> 总投放数量,填 0 为不限制总领取数,<i style="color: red;font-style: normal;">保存后不可修改</i> </span></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="form-group form-group-sm col-sm-12">
                <label>使用期限：</label>
                <div>
                    <input type="text" class="form-control _startTime" readonly autocomplete="off" name="start_time" size="18" value="{$info.start_time}" placeholder="开始时间"/> 至 <input type="text" class="form-control _endTime" readonly autocomplete="off" size="18" name="expire_time" value="{$info.expire_time}" placeholder="过期时间"/>
                    <div><span class="help-block m-t-xs"><i class="wb-info-circle"></i> 可使用时间范围</span></div>
                </div>
            </div>
            <div class="form-group form-group-sm col-sm-12">
                <label>状态：</label>
                <div>
                    {foreach from=$status_type key=key item=item}
                        <label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>
                    {/foreach}
                </div>
            </div>
            <div class="form-group form-group-sm col-sm-12">
                <button type="submit" class="btn btn-primary-outline">提交</button>
            </div>
        </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        layui.use('laydate',function () {
            layui.laydate.render({
                elem: '._startTime',
                type:'datetime'
            });
            layui.laydate.render({
                elem: '._endTime',
                type:'datetime'
            });
        });
    });
</script>
{include 'footer.tpl'}