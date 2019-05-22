{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>菜单管理</h5>
                <div class="ibox-tools">
                    {if $permission.modify}<a href="/menu/modify" class="btn btn-xs btn-primary" data-rel="ajax"><i class="fa fa-plus"></i> 添加菜单</a> {/if}
                    {if $permission.publish}<a href="/menu/publish" class="btn btn-xs btn-primary" data-rel="ajax"><i class="fa fa-upload"></i> 发布菜单</a> {/if}
                    {if $permission.getmenu}<a href="/menu/getmenu" class="btn btn-xs btn-primary" data-rel="ajax"><i class="fa fa-search"></i> 查看菜单</a> {/if}
                </div>
            </div>
            <div class="ibox-content clearfix">
                <form method="post" action="/menu/order" data-rel="ajax">
                <table class="table table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th width="55">排序</th>
                        <th>名称</th>
                        <th>动作</th>
                        <th width="100" class="text-center">类型</th>
                        {if $permission.modify || $permission.delete}<th width="105" class="text-center">操作</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {$categorys}
                    </tbody>
                </table>
                <div class="form-group form-group-sm"><button type="submit" class="btn btn-primary btn-sm">更新排序</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}