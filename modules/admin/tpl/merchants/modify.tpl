{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        {if !$_F['in_ajax']}<div class="ibox"><div class="ibox-title"><h5>{$page_title}</h5></div><div class="ibox-content clearfix">{/if}
            <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="dialog-btn-from">
            <div class="tabs-container">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true">基本信息</a></li>
                </ul>
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="panel-body" style="padding: 15px 20px;">
                            <div class="form-group form-group-sm">
                                <table width="100%">
                                    <tbody>
                                    <tr>
                                        <td width="33%">
                                            <div class="col-sm-12 p-l-none">
                                                <label><span class="text-danger">*</span> 登录账号：</label>
                                                <div>
                                                    <input name="xxx" value="{$info.username}"{if $info.uid gt 0} readonly{/if} class="form-control" autocomplete="off" placeholder="登录账号"/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-sm-12 p-l-none">
                                                <label><span class="text-danger">*</span> 登录密码：</label>
                                                <div>
                                                    <input name="aaa" value="" type="text" autocomplete="off" class="form-control" placeholder="登录密码(不修改留空)"/>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group form-group-sm">
                                <table width="100%">
                                    <tbody>
                                    <tr>
                                        <td width="33%">
                                            <div class="col-sm-12 p-l-none">
                                                <label><span class="text-danger">*</span> 商户称呼：<small>(对外显示)</small></label>
                                                <div>
                                                    <input name="truename" value="{$info.truename}" class="form-control" placeholder="商户称呼"/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-sm-12 p-l-none">
                                                <label>真实称呼：<small>(仅后台使用)</small></label>
                                                <div>
                                                    <input name="real_name" value="{$info.real_name}" class="form-control" placeholder="真实称呼"/>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group form-group-sm">
                                <table width="100%">
                                    <tbody>
                                    <tr>
                                        <td width="33%">
                                            <div class="col-sm-12 p-l-none">
                                                <label><span class="text-danger">*</span> 绑定微信：<small>(会员UID)</small></label>
                                                <div>
                                                    {if !$is_super}
                                                        <label>{$info.user_id}</label>
                                                    {else}
                                                        <input name="user_id" value="{$info.user_id}" type="text" autocomplete="off" class="form-control" placeholder="会员UID(不要随意修改)"/>
                                                    {/if}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-sm-12 p-l-none">
                                                <label>账号类型：</label>
                                                <div>
                                                    <select class="form-control _switchAccount" name="type" title="账号类型" style="width: 130px;">
                                                        {foreach from=$merchants_type key=key item=item}
                                                            <option value="{$key}"{if $key eq $info.type} selected="selected"{/if}>{$item}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group form-group-sm">
                                <table width="100%">
                                    <tbody>
                                    <tr>
                                        <td width="33%">
                                            <div class="col-sm-12 p-l-none">
                                                <label><span class="text-danger">*</span> 手机号码：</label>
                                                <div>
                                                    <input name="mobile" value="{$info.mobile}" class="form-control" placeholder="手机号码"/>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-sm-12 p-l-none">
                                                <label>邮箱：</label>
                                                <div>
                                                    <input name="email" value="{$info.email}" class="form-control" size="25" placeholder="邮箱地址"/>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group form-group-sm">
                                <table width="100%">
                                    <tbody>
                                    <tr>
                                        <td width="33%">
                                            <div class="col-sm-12 p-l-none">
                                                <label>账户状态：</label>
                                                <div>
                                                    {if !$is_super}
                                                        <label>{$status_type[$info.status]}</label>
                                                    {else}
                                                        {foreach from=$status_type key=key item=item}
                                                            <label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>
                                                        {/foreach}
                                                    {/if}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="dialog-btn-group">
                <button type="submit" class="btn btn-primary-outline"><i class="wb-upload"></i> 保存</button>
            </div>
        </form>
        {if !$_F['in_ajax']}</div></div>{/if}
    </div>
</div>
{include "footer.tpl"}