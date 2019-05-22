{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-content clearfix" style="padding: 20px;">
                <form data-rel="ajax" method="post" action="{$formData.url}" class="form-horizontal dialog-btn-from" role="form">
                    <div class="form-group form-inline">
                        <div class="input-group">
                            <span class="input-group-addon">登录账号</span>
                            <input name="xxx" value="{$info.username}" class="form-control" autocomplete="off" placeholder="登录账号"/>
                        </div>
                    </div>
                    <div class="form-group form-inline">
                        <div class="input-group">
                            <span class="input-group-addon">登录密码</span>
                            <input name="aaa" value="" type="text" autocomplete="off" class="form-control" placeholder="登录密码"/>
                        </div>
                    </div>
                    <div class="form-group form-inline">
                        <div class="input-group">
                            <span class="input-group-addon">用户称呼</span>
                            <input name="truename" value="{$info.truename}" class="form-control" placeholder="用户称呼"/>
                        </div>
                    </div>
                    <div class="form-group form-inline">
                        <div class="input-group">
                            <span class="input-group-addon">真实姓名</span>
                            <input name="real_name" value="{$info.real_name}" class="form-control" placeholder="真实姓名"/>
                        </div>
                    </div>
                    <div class="form-group form-inline">
                        <div class="input-group">
                            <span class="input-group-addon">手机号码</span>
                            <input name="mobile" value="{$info.mobile}" class="form-control" placeholder="手机号码"/>
                        </div>
                    </div>
                    <div class="form-group form-inline">
                        <div class="input-group">
                            <span class="input-group-addon">Q Q号 码</span>
                            <input name="qq" value="{$info.qq}" class="form-control" placeholder="QQ号码"/>
                        </div>
                    </div>
                    <div class="form-group form-group-sm">
                        <span>账号类型：</span>
                        {foreach from=$account_type key=key item=item}
                            <label><input type="radio" name="account_type" value="{$key}"{if $key eq $info.account_type} checked="checked"{/if}/> {$item}</label>
                        {/foreach}
                    </div>
                    <div class="form-group form-group-sm">
                        <span>所属职位：</span>
                        <select name="position_id" title="所属职位" class="form-control">
                            {$position_select}
                        </select>
                    </div>
                    <div class="form-group form-group-sm">
                        <span>状态：</span>
                        {foreach from=$status_type key=key item=item}
                            <label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>
                        {/foreach}
                    </div>
                    <div class="dialog-btn-group">
                        <button type="submit" class="btn btn-primary-outline">提交</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{include "footer.tpl"}