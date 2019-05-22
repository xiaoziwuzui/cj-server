{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-content clearfix">
                <div class="detail-review">
                    <div class="review-text" id="review-text" style="text-align: left;">
                        <p><span>车辆牌照：</span>{$info.plate}</p>
                        <p><span>车头照片</span></p>
                        <p style="text-align: left;">{if $info.image eq ''}无图{else}<img src="{$info.image}" style="width: 620px;display: inline-block;" />{/if}</p>
                        {if $order.total gt 0}
                            <p><span>缴费次数：</span>{$order.total}</p>
                            <p><span>缴费总金额：</span>{Service_Public::formatMoney($order.pay_money)}</p>
                        {/if}
                    </div>
                    <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal m-t-sm">
                        <div class="form-group col-sm-12" style="padding-bottom: 10px;">
                            <div>
                                <select name="level" class="form-control _chosen" title="车辆类别">
                                    <option value="">车辆类别</option>
                                    {foreach from=$plate_level item=item key=key}
                                        <option value="{$key}"{if $smarty.get.level eq $key} selected="selected"{/if}>{strip_tags($item)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="dialog-btn-group">
                            <button type="submit" class="btn btn-primary-outline">提交</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}