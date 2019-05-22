{include "header.tpl"}
<form rel="ajax" method="post" action="{$formData.url}" data-rel="ajax" role="form">
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">要设置的职位</label>
        <select name="setid" class="form-control" title="当前职位">
            {foreach from=$list item=item key=key}
                <option value="{$item.position_id}">{$item.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group form-group-sm">
        <label class="control-label p-r-n">复制权限的职位</label>
        <select name="copyid" class="form-control" title="对向职位">
            {foreach from=$list item=item key=key}
                <option value="{$item.position_id}">{$item.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">保存</button>
    </div>
</form>
{include "footer.tpl"}