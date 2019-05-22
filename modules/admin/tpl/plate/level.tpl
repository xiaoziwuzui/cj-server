{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-content clearfix">
                <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal m-t-sm">
                    <div class="form-group col-sm-12" style="padding-bottom: 10px;">
                        <div>
                            <label>车辆分级：</label>
                            <select name="level" class="form-control _chosen" title="车辆类别">
                                <option value="">车辆类别</option>
                                {foreach from=$plate_level item=item key=key}
                                    <option value="{$key}"{if $smarty.get.level eq $key} selected="selected"{/if}>{strip_tags($item)}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="dialog-btn-group">
                        <button type="submit" class="btn btn-primary-outline">提交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}