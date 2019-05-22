{if !$_F['in_ajax']}<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>{$title}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="shortcut icon" href="{$ssl_domain}/favicon.png">
    <link rel="apple-touch-icon" href="{$ssl_domain}/favicon.png">
    <link rel="stylesheet" href="{$ssl_assets}plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/web-icons/css/web-icons.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/layui/css/layui.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/animate/animate.min.css">
    <link rel="stylesheet" href="{$ssl_assets}css/style.min.css?v={$unit_version}">
    <link rel="stylesheet" href="{$ssl_assets}css/common.min.css?v={$unit_version}">
    <!--[if lt IE 9]>
    <script type="text/javascript" src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script type="text/javascript" src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script data-type="{$ssl_assets}" type="text/javascript" src="{$ssl_assets}js/set.js?v={$unit_version}"></script>
    <link rel="stylesheet" type="text/css" href="{$ssl_assets}plugins/chosen/chosen.min.css">
    <script type="text/javascript" src="{$ssl_assets}plugins/chosen/chosen.jquery.min.js?v={$unit_version}"></script>
{foreach from=$set_assets item=item key=key}
    {if FFile::getFileExtion($item) eq 'js'}
        <script type="text/javascript" src="{$ssl_assets}{$item}"></script>
    {else}
        <link rel="stylesheet" type="text/css" href="{$ssl_assets}{$item}" />
    {/if}
{/foreach}
    {if $original_check}<script type="text/javascript" src="{$ssl_assets}js/app/original.js?v={$unit_version}s"></script><script>var _Original_init = true;</script>{/if}
    <script type="text/javascript">
        var userSetting = {json_encode($_F['member']['setting'])},_uid = parseInt('{$_F['uid']}');
        if(!userSetting){
            userSetting = { };
        }
        jQuery(document).ready(function (e) {
            if(typeof(layui) !== "undefined"){
                layui.config({ dir: '{$ssl_assets}plugins/layui/'});
                layui.use(['layer','laydate'], function(){
                    var layer = layui.layer,laydate = layui.laydate;
                    unit.init();
                });
            }else{
                alert('JS文件被无耻的劫持了,刷新再试下吧')
            }
        });
    </script>
</head>{/if}