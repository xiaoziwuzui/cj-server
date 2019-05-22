<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp">
    <title>{$title}</title>
    <link href="{$ssl_assets}images/favicon.png" rel="shortcut icon">
    <link href="{$ssl_assets}images/favicon.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="{$ssl_assets}plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/font-awesome/font-awesome.min.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/animate/animate.min.css">
    <link rel="stylesheet" href="{$ssl_assets}css/style.min.css">
    <link rel="stylesheet" href="{$ssl_assets}plugins/iCheck/custom.css">
    <style type="text/css">.nav-second-level li a{ cursor:pointer; }</style>
</head>
<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden;">
<div id="wrapper">
    <!--左侧导航开始-->
    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="nav-close">
            <i class="fa fa-times-circle"></i>
        </div>
        <div class="sidebar-collapse">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0;background: none;">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#">
                        <i class="fa fa-bars"></i>
                    </a>
                </div>
            </nav>
            <ul class="nav" id="side-menu">
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="clear">
                                <span class="block m-t-xs"><strong class="font-bold" style="text-transform: capitalize;">{$manager.truename}：{$manager.uid}</strong></span>
                                <span class="text-muted text-xs block">{$permission_name} <b class="caret"></b></span>
                            </span>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a class="J_menuItem" href="/main/edit">修改资料</a></li>
                            <li class="divider"></li>
                            <li><a href="/auth/logout">安全退出</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">高</div>
                </li>
                {foreach from=$menu key=key item=item}
                    {if isset($item.menu)}
                        <li{if $key eq 0} class="active"{/if}>
                            <a href="javascript:void(0);"><i class="fa {$item.icon}"></i>
                                <span class="nav-label">{$item.name}</span>
                                <span class="fa arrow"></span>
                            </a>
                            <ul class="nav nav-second-level">
                                {foreach from=$item.menu key=mk item=mval}
                                    <li><a class="J_menuItem" href="{$mval.url}">{$mval.name}</a></li>
                                {/foreach}
                            </ul>
                        </li>
                    {else}
                        <li{if $key eq 0} class="active"{/if}><a href="{$item.url}" class="J_menuItem"><i class="fa {$item.icon}"></i><span class="nav-label">{$item.name}</span></a></li>
                    {/if}
                {/foreach}

            </ul>
        </div>
    </nav>
    <!--左侧导航结束-->
    <!--右侧部分开始-->
    <div id="page-wrapper" class="gray-bg dashbard-1">
        <div class="row content-tabs">
            <button class="roll-nav roll-left J_tabLeft">
                <i class="fa fa-backward"></i>
            </button>
            <nav class="page-tabs J_menuTabs">
                <div class="page-tabs-content">
                    <a href="javascript:void(0);" class="active J_menuTab" data-id="{$mainInfo.url}"> {$mainInfo.name}</a>
                </div>
            </nav>
            <button class="roll-nav roll-right J_tabRight">
                <i class="fa fa-forward"></i>
            </button>
            <div class="btn-group roll-nav roll-right">
                <button class="dropdown J_tabClose" data-toggle="dropdown"><i class="fa fa-cogs"></i> <span class="caret"></span></button>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li class="J_tabRefresh"><a><i class="fa fa-refresh"></i> 刷新页面</a></li>
                    <li class="J_tabShowActive"><a>定位当前选项卡</a></li>
                    <li class="divider"></li>
                    <li class="J_tabCloseAll"><a>关闭全部选项卡</a></li>
                    <li class="J_tabCloseOther"><a>关闭其他选项卡</a></li>
                </ul>
            </div>
            <a href="/auth/logout" class="roll-nav roll-right J_tabExit"><i class="fa fa fa-sign-out"></i>退出</a>
        </div>
        <div class="row J_mainContent" id="content-main">
            <iframe class="J_iframe" name="iframe0" width="100%" height="100%" src="{$mainInfo.url}" frameborder="0" data-id="{$mainInfo.url}" seamless></iframe>
        </div>
    </div>
</div>
<script type="text/javascript" src="{$ssl_assets}js/set.js?v={$unit_version}"></script>
<script type="text/javascript" src="{$ssl_assets}plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="{$ssl_assets}plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="{$ssl_assets}plugins/h-plus/hplus.min.js?t=1"></script>
<script type="text/javascript" src="{$ssl_assets}plugins/h-plus/contabs.js?t=2"></script>
<script type="text/javascript" src="{$ssl_assets}plugins/pace/pace.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(e){
        $('.dropdown>.dropdown-menu>li>a').click(function (e) {
            var $parent = $('.dropdown');
            if($parent.hasClass('open')){
                $parent.removeClass('open');
                $parent.find('a.dropdown-toggle').attr('aria-expanded',false);
            }
        });
    });
</script>
</body>
</html>

