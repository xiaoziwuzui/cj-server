{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-content clearfix">
                <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal">
                    <div class="form-group col-sm-12">
                        <label>菜单名称：</label>
                        <div>
                            <input name="name" value="{$info.name}" size="40" class="form-control" placeholder="菜单名称"/>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>菜单类型：</label>
                        <div>
                            <select name="type" title="菜单类型" class="form-control _switchCat">
                                {foreach from=$type_map key=key item=item}
                                    <option value="{$key}"{if $key eq $info.type} selected="selected"{/if}>{$item}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>上级菜单：</label>
                        <div>
                            <select name="parent_id" title="上级菜单" class="form-control">
                                <option value="0"{if 0 eq $info.parent_id} selected="selected"{/if}>顶级菜单</option>
                                {foreach from=$parent key=key item=item}
                                    <option value="{$item.id}"{if $item.id eq $info.parent_id} selected="selected"{/if}>{$item.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>菜单KEY值：</label>
                        <div>
                            <input name="key" value="{$info.key}" size="60" class="form-control" placeholder="菜单KEY值"/>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>跳转链接：</label>
                        <div>
                            <input name="url" value="{$info.url}" size="60" class="form-control" placeholder="跳转链接"/>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>排序：</label>
                        <div>
                            <input name="list_order" value="{$info.list_order}" size="10" class="form-control" placeholder="排序"/>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <div>
                              <button type="submit" class="btn btn-primary">保存菜单</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}