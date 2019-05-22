{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <form role="form" action="{$formData.url}" method="POST" data-rel="ajax" class="form-horizontal">
            <div class="ibox float-e-margins">
                {include 'page_title.tpl'}
                <div class="ibox-content clearfix">
                    <div class="tabs-container">
                        <ul class="nav nav-tabs _userInfoTabs">
                            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true">基本设置</a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane active">
                                <div class="panel-body">
                                    {foreach from=$_config item=item key=key}
                                        <div class="form-group col-sm-12">
                                            <label>{$item.name}：</label>
                                            <div>
                                                {if $item.type eq 'text'}
                                                    <input name="setting[{$key}]"{if isset($item['size'])} size="{$item.size}"{/if} value="{$data[$key]}" class="form-control" placeholder="{$item.name}"/>
                                                {/if}
                                                {if $item.type eq 'textarea'}
                                                    <textarea name="setting[{$key}]"{if isset($item['size'])} cols="{$item.size}"{/if}{if isset($item['rows'])} rows="{$item.rows}"{/if} placeholder="{$item.name}" class="form-control">{$data[$key]}</textarea>
                                                {/if}
                                                {if $item.type eq 'checkbox'}
                                                    {foreach from=$item.options item=oitem key=okey}
                                                        <label style="font-weight: normal;">
                                                            <input type="checkbox"{if in_array($okey,$data[$key])} checked{/if} name="setting[{$key}][]" value="{$okey}"> {$oitem}</label>
                                                    {/foreach}
                                                {/if}
                                                {if $item.type eq 'radio'}
                                                    {foreach from=$item.options item=oitem key=okey}
                                                        <label style="font-weight: normal;">
                                                            <input type="radio"{if $data[$key] eq $okey} checked{/if} name="setting[{$key}]" value="{$okey}"> {$oitem}</label>
                                                    {/foreach}
                                                {/if}
                                                {if isset($item['tips'])}
                                                    <p class="help-info m-t-sm" style="color: #575757;"> {$item.tips}</p>
                                                {/if}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-sm-12 m-t-sm">
                        <button type="submit" class="btn btn-primary clearInput btn-outline"><i class="fa fa-save"></i>  <span class="bold">保存修改</span></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
{include 'footer.tpl'}