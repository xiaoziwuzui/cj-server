{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        {if !$_F['in_ajax']}<div class="ibox"><div class="ibox-title"><h5>{$page_title}</h5></div><div class="ibox-content clearfix">{/if}
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true">基本信息</a></li>
            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="panel-body" style="padding: 15px 20px;">
                        <div class="form-group form-group-sm">
                            <table>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="col-sm-12 p-l-none">
                                            <label> 订&nbsp;&nbsp;单&nbsp;&nbsp;号：{$data.order_no}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-sm-12">
                                            <label>车&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;牌：{$data.plate}</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="col-sm-12 p-l-none">
                                            <label>创建时间：{$data.create_time|date_format:'Y-m-d H:i:s'}</label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="col-sm-12">
                                            <label>支付状态：{$status_type[$data.status]}</label>
                                        </div>
                                    </td>
                                </tr>
                                {if $data.status gt 0}
                                <tr>
                                    <td colspan="2">
                                        <div class="col-sm-12 p-l-none">
                                            <label>付款(处理)时间：{if $data.pay_time gt 0}{$data.pay_time|date_format:'Y-m-d H:i:s'}{else}--{/if}</label>
                                        </div>
                                    </td>
                                </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                        <div class="form-group form-group-sm">
                            {$data.detail.format}
                        </div>
                        <div class="form-group form-group-sm">
                            <p style="text-align: left;">{if $data.image neq ''}<a href="{$data.image}" target="_blank"><img src="{$data.image}" style="width: 320px;display: inline-block;" /></a>{/if}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {if !$_F['in_ajax']}</div></div>{/if}
    </div>
</div>
{include "footer.tpl"}