{include "header.tpl"}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal">
            <div class="form-group col-sm-12">
                <label class="{$_form_label}">部门名称：</label>
                <div class="{$_form_input}">
                    <input name="name" value="{$info.name}" class="form-control" autocomplete="off" placeholder="部门名称"/>
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label class="{$_form_label}">上级部门：</label>
                <div class="{$_form_input}">
                    <select name="parent_id" title="上级部门" class="form-control">
                        {if $info.parent_id eq 0}<option value="0" selected>顶级部门</option>{/if}
                        {$department_select}
                    </select>
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label class="{$_form_label}">状态：</label>
                <div class="{$_form_input}">
                {foreach from=$status_type key=key item=item}
                    <label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>
                {/foreach}
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label>部门权限设置： <button class="btn-xs btn-primary toggle_setp" type="button" data-toggle="0">展开</button></label>
                <div class="setp" style="display: none; clear: both;">
                {foreach from=$permissions key=pkey item=permission}
                    <fieldset class="wap-setting">
                        <legend style="font-size: 14px;font-weight: normal;padding-bottom: 5px;margin-bottom: 0;">{ucfirst($pkey)} <span class="text-success _select_all_btn" style="cursor: pointer;">全选/反选</span></legend>
                        {if $info.department_id gt 0 || $info.parent_id eq 0}
                            {foreach from=$permission key=key item=item}
                                <label style="font-weight: normal;{if in_array(strtolower($key),$info.department_permission)}color:red;{/if}" title="{$key}"><input type="checkbox" name="set_permission[]" value="{strtolower($key)}"{if in_array(strtolower($key),$info.department_permission)} checked="checked"{/if}/> {$item} &nbsp;&nbsp;</label>
                            {/foreach}
                        {else}
                            {foreach from=$permission key=key item=item}
                                <label style="font-weight: normal;" title="{$key}"><input type="checkbox" name="set_permission[]" value="{strtolower($key)}" checked="checked"/> {$item} &nbsp;&nbsp;</label>
                            {/foreach}
                        {/if}
                    </fieldset>
                {/foreach}
                </div>
            </div>
            <div class="form-group col-sm-12">
                <label class="{$_form_label}">下级权限：</label>
                <div class="{$_form_input}">
                    <label style="font-weight: normal;"><input type="checkbox" name="childer_add" value="1" checked/> 新增批量继承</label>
                    <label style="font-weight: normal;"><input type="checkbox" name="childer_delete" value="1" checked/> 删除批量取消</label>
                    <div class="row m-t-sm">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function (e) {
        $('span._select_all_btn').on('click',this,function (evt) {
            var $parent = $(evt.currentTarget).parent().parent();
            $parent.find('input').each(function (index,item) {
                $(item).prop('checked',!$(item).prop('checked'));
            });
        });
        $('.toggle_setp').on('click',this,function (evt) {
            var $el=$(evt.currentTarget),toggle=$el.data('toggle'),$divel=$('.setp');
            if(parseInt(toggle) === 0){
                $divel.show();
                $el.data('toggle',9).text('收起');
            }else{
                $divel.hide();
                $el.data('toggle',0).text('展开');
            }
        });
    });
</script>
{include "footer.tpl"}