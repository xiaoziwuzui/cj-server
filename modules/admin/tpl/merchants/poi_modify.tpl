{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        {if !$_F['in_ajax']}<div class="ibox"><div class="ibox-title"><h5>{$page_title}</h5></div><div class="ibox-content clearfix">{/if}
            <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal dialog-btn-from">
                <div class="form-group col-sm-12">
                    <table width="auto">
                        <tbody>
                        <tr>
                            <td>
                                <div class="col-sm-12 form-group-sm">
                                    <label>门店名称：</label>
                                    <div>
                                        <select name="mid" class="form-control _chosen" title="所属商户">
                                            <option value="">所属商户</option>
                                            {foreach from=$merchants_list item=item key=key}
                                                <option value="{$key}"{if $info.mid eq $key} selected="selected"{/if}>{$item.real_name}</option>
                                            {/foreach}
                                        </select>
                                        <input name="business_name" value="{$info.business_name}" size="30" class="form-control" placeholder="仅为商户名，如：国美、麦当劳"/>
                                        <div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 15个汉字或30个英文字符内</span></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="col-sm-12 form-group-sm">
                                    <label>分店名称：</label>
                                    <div>
                                        <input name="branch_name" value="{$info.branch_name}" size="30" class="form-control" placeholder="分店名称"/>
                                        <div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 20 个字 以内</span></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-group col-sm-12">
                    <table width="auto">
                        <tbody>
                        <tr>
                            <td>
                                <div class="col-sm-12 form-group-sm">
                                    <label>区域位置：</label>
                                    <div id="distpicker">
                                        <select name="province" class="form-control" data-province="{$info.province}" title="选择省份"></select>
                                        <select name="city"  class="form-control" data-city="{$info.city}" title="选择城市"></select>
                                        <select name="area" class="form-control" data-district="{$info.district}" title="选择区域"></select>
                                        <div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 选择门店行政区域</span></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="col-sm-12 form-group-sm">
                                    <label>街道地址：</label>
                                    <div>
                                        <input name="address" type="text" class="form-control" value="{$userInfo.address}" placeholder="地址（可选项）" />
                                        <div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 门店所在的详细街道地址（不要填写省市信息）</span></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>


                {$thumbUpload}
                <div class="form-group col-sm-12">
                    <table>
                        <tbody>
                        <tr>
                            <td>
                                <div class="col-sm-12">
                                    <label>任务编号：</label>
                                    <div>
                                        <input name="taskid" value="{$info.taskid}" readonly size="6" class="form-control" placeholder="任务编号"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="col-sm-12">
                                    <label>作者ID：</label>
                                    <div>
                                        <input name="uid" value="{$info.uid}" size="6" class="form-control" placeholder="作者ID"/>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="col-sm-12">
                                    <label>本篇佣金：</label>
                                    <div>
                                        <input name="money" value="{$info.money}" size="6" class="form-control" placeholder="本篇佣金"/>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-12">
                    <label>地图坐标：</label>
                    <div>
                        <input name="longitude_latitude" value="{$info.longitude_latitude}" size="30" class="form-control" placeholder="高德地图坐标"/>
                        <div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 必须为高德地图坐标, <a href="https://lbs.amap.com/console/show/picker" target="_blank" class="text-info">在此拾取</a>,格式为 : 112.938888,28.228272</span></div>
                    </div>
                </div>
                <div class="form-group col-sm-12">
                    <label>状态：</label>
                    <div>
                        {if $permission.super}
                            {foreach from=$status_type key=key item=item}
                                {if $key neq 9}<label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>{/if}
                            {/foreach}
                        {else}
                            <label>{$status_type[$info.status]}</label>
                        {/if}
                    </div>
                </div>
                <div class="dialog-btn-group">
                    <button type="submit" class="btn btn-primary-outline"><i class="wb-upload"></i> 保存</button>
                </div>
            </form>

        {if !$_F['in_ajax']}</div></div>{/if}
    </div>
</div>
<script src="{$ssl_assets}plugins/city/distpicker.data.js"></script>
<script src="{$ssl_assets}plugins/city/distpicker.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var $distpicker = $('#distpicker');
        $distpicker.distpicker({
            province: '湖南省',
            city: '长沙市',
            district: '天心区'
        });
    });
</script>
{include "footer.tpl"}