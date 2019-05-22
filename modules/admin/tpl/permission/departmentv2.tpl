{include 'header.tpl'}
<link type="text/css" rel="stylesheet" href="{$ssl_assets}plugins/treeview/jquery.treeview.css" />
<script type="text/javascript" src="{$ssl_assets}plugins/treeview/jquery.treeview.js"></script>
<script type="text/javascript" src="{$ssl_assets}plugins/treeview/jquery.treeview.edit.js"></script>
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>权限设置</h5>
                <div class="ibox-tools m-r-xs">
                    {if $permission.departmentmodify}
                        <button type="button" class="btn btn-primary btn-xs" href="/admin/permission/departmentmodify" data-rel="ajax"><i class="glyphicon glyphicon-th-list"></i> 新增部门</button>
                    {/if}
                    {if $permission.positionmodify}
                        <button type="button" class="btn btn-primary btn-xs" href="/admin/permission/positionmodify" data-rel="ajax"><i class="glyphicon glyphicon-user"></i> 新增职位</button>
                    {/if}
                    {if $permission.copyposition}
                        <button type="button" class="btn btn-primary btn-xs" href="/admin/permission/copyposition" data-width="320" data-height="320" data-rel="ajax"><i class="glyphicon glyphicon-user"></i> 复制职位</button>
                    {/if}
                </div>
            </div>
            <div class="ibox-content clearfix">
                {$department}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function (e) {
        $("#browser").treeview();
        $(".treeview .file").hover(function(){
            var id = $(this).data('id'),type=$(this).data('type');
            $('#'+type+'_file' + id).show();
        },function(){
            var id = $(this).data('id'),type=$(this).data('type');
            $('#'+type+'_file' + id).hide();
        });
        $(".treeview .folder").hover(function(){
            var id = $(this).data('id'),type=$(this).data('type');
            $('#'+type+'_folder' + id).show();
        },function(){
            var id = $(this).data('id'),type=$(this).data('type');
            $('#'+type+'_folder' + id).hide();
        });
    });
</script>
{include 'footer.tpl'}