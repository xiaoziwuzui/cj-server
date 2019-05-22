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
                    <div class="panel-body" style="padding: 15px 10px;">
                        <div class="form-group form-group-sm m-b-sm">
                            <table width="100%">
                                <tbody>
                                <tr>
                                    <td width="64px">
                                        <div class="col-sm-12 p-l-none">
                                            <div>
                                                {if $info.avatar eq ''}无图{else}<a href="{$info.avatar}" target="_blank" title="点击查看大图"><img src="{$info.avatar}" style="max-width: 60px;max-height: 60px;object-fit: cover;" /></a>{/if}
                                            </div>
                                        </div>
                                    </td>
                                    <td valign="top">
                                        <div class="col-sm-12 m-b-sm">
                                            昵称：{$info.nickname}
                                        </div>
                                        <div class="col-sm-12 m-b-sm">
                                            性别：{$member_sex[$info.sex]}
                                        </div>
                                        <div class="col-sm-12">
                                            地理：{if $info.area_info eq ''}无{else}{$info.area_info}{/if}
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group form-group-sm m-b-sm">
                            <table width="100%">
                                <tbody>
                                <tr>
                                    <td width="33%">
                                        <div class="col-sm-12 p-l-none">
                                            <label>关注时间：</label>
                                            <div>
                                                <label>{$info.register_time|date_format:"Y-m-d H:i:s"}</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="33%">
                                        <div class="col-sm-12">
                                            <label>最后活跃：</label>
                                            <div>
                                                <label>{$info.last_time|date_format:"Y-m-d H:i:s"}</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-sm-12">
                                            <label>关注状态：</label>
                                            <div>
                                                <label>{$status_type[$info.status]}</label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group form-group-sm m-b">
                            <table width="100%">
                                <tbody>
                                <tr>
                                    <td width="33%">
                                        <div class="col-sm-12 p-l-none">
                                            <label><span class="text-danger">*</span> 手机号码</label>
                                            <div>
                                                {if !$is_super}
                                                    <label>{if $info.mobile eq ''}无{else}{$info.mobile}{/if}</label>
                                                {else}
                                                    <input name="mobile" value="{$info.mobile}" type="text" autocomplete="off" class="form-control" placeholder="手机号码"/>
                                                {/if}
                                            </div>
                                        </div>
                                    </td>
                                    <td width="33%">
                                        <div class="col-sm-12">
                                            <label>邮箱</label>
                                            <div>
                                                <input name="email" value="{$info.email}" class="form-control" placeholder="邮箱地址"/>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-sm-12">
                                            <label>真实姓名</label>
                                            <div>
                                                <input name="truename" value="{$info.truename}" class="form-control" placeholder="真实姓名"/>
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