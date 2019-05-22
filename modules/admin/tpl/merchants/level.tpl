{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        {if !$_F['in_ajax']}<div class="ibox"><div class="ibox-title"><h5>设置商户级别</h5></div><div class="ibox-content clearfix">{/if}
        <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal">
            <div class="form-group col-sm-12">
                <label>商户级别：</label>
                <div>
                    {foreach from=$member_level key=key item=item}
                        <label><input type="radio" name="level" value="{$key}"{if $key eq $info.level} checked="checked"{/if}/> {$item}</label>
                    {/foreach}
                </div>
            </div>
            <div class="form-group col-sm-12">
                <div >
                    <button type="submit" class="btn btn-primary">设置</button>
                </div>
            </div>
        </form>
        {if !$_F['in_ajax']}</div></div>{/if}
    </div>
</div>
{include 'footer.tpl'}