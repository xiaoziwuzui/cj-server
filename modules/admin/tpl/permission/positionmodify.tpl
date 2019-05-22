{include "header.tpl"}
<form rel="ajax" method="post" action="{$formData.url}" data-rel="ajax" role="form">
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">职位名称</label>
        <input name="name" value="{$info.name}" class="form-control" autocomplete="off" placeholder="职位名称"/>
    </div>
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">所属部门</label>
        <select name="department_id" title="所属部门" class="form-control">
            {$department_select}
        </select>
    </div>
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">上级职位</label>
        <select name="parent_id" title="上级职位" class="form-control _switchAccount">
            {if $info.parent_id eq 0}<option value="0" selected>顶级职位</option>{/if}
            {$position_select}
        </select>
    </div>
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">上级用户</label>
        <select name="parent_uid" title="上级用户" class="form-control">
			{if $info.parent_uid eq 0}<option value="0" selected>顶级用户</option>{/if}
			{foreach from=$userlist key=key item=item}
                <option value="{$item.uid}"{if $item.uid eq $info.parent_uid} selected="selected"{/if}>{$item.truename}</option>
			{/foreach}
        </select>
        <p>仅对只能查看自己和下属用户数据的职位生效</p>
    </div>
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">数据查看：</label>
		{foreach from=$account_type key=key item=item}
            <label><input type="radio" name="account_type" value="{$key}"{if $key eq $info.account_type} checked="checked"{/if}/> {$item}</label>
		{/foreach}
    </div>
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">状态：</label>
		{foreach from=$status_type key=key item=item}
            <label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>
		{/foreach}
    </div>

    <div class="form-group">
        <label>职位权限设置</label>  <button class="btn-xs btn-primary toggle_setp" type="button" data-toggle="0">展开</button>
        <div class="setp" style="display: none;">
        {foreach from=$permissions key=pkey item=permission}
            <fieldset class="wap-setting">
                <legend style="font-size: 14px;font-weight: normal;padding-bottom: 5px;margin-bottom: 0;">{ucfirst($pkey)} <span class="text-success _select_all_btn" style="cursor: pointer;">全选/反选</span></legend>
				{if $info.position_id gt 0 || $info.parent_id eq 0}
					{foreach from=$permission key=key item=item}
                        <label style="font-weight: normal;{if in_array(strtolower($key),$info.position_permission)}color:red;{/if}" title="{$key}"><input type="checkbox" name="set_permission[]" value="{strtolower($key)}"{if in_array(strtolower($key),$info.position_permission)} checked="checked"{/if}/> {$item} &nbsp;&nbsp;</label>
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
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">下级权限：</label>
        <label style="font-weight: normal;"><input type="checkbox" name="childer_add" value="1" checked/> 新增批量继承</label>
        <label style="font-weight: normal;"><input type="checkbox" name="childer_delete" value="1" checked/> 删除批量取消</label>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">保存</button>
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
        $('._switchAccount').on('change',this,function (evt) {
            var id= parseInt($(evt.currentTarget).val()),$setel=$('select[name="parent_uid"]');
            if(!isNaN(id) && id > 0){
                $.getJSON('/admin/permission/getpositionuser',{ position_id:id},function (result) {
                    var html = '';
                    for (var i=0;i<result.res.length;i++){
                        html += '<option value="'+result.res[i].uid+'">'+result.res[i].truename+'</option>';
                    }
                    $setel.html(html);
                });
            }
        });
    });
</script>
{include "footer.tpl"}