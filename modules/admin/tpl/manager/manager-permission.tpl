{include "header.tpl"}
<form rel="ajax" method="post" action="{$formData.url}" data-rel="ajax" role="form">
    <div class="form-group">
        <label>用户权限设置</label>
		{foreach from=$permissions key=pkey item=permission}
            <fieldset class="wap-setting">
                <legend style="font-size: 14px;font-weight: normal;padding-bottom: 5px;margin-bottom: 0;">{ucfirst($pkey)} <span class="text-success _select_all_btn" style="cursor: pointer;">全选/反选</span></legend>
					{foreach from=$permission key=key item=item}
                        <label style="font-weight: normal;{if in_array(strtolower($key),$manager_permission)}color:red;{/if}" title="{$key}"><input type="checkbox" name="set_permission[]" value="{strtolower($key)}"{if in_array(strtolower($key),$manager_permission)} checked="checked"{/if}/> {$item} &nbsp;&nbsp;</label>
					{/foreach}
            </fieldset>
		{/foreach}
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">提交</button>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function (e) {
        $('span._select_all_btn').on('click',this,function (evt) {
            var $parent = $(evt.currentTarget).parent().parent();
            $parent.find('input').each(function (index,item) {
                $(item).prop('checked',!$(item).prop('checked'));
            });
        });
    });
</script>
{include "footer.tpl"}