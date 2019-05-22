{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-title">
                <h5>部门结构管理</h5>
                <div class="ibox-tools m-r-xs">
                    {if $permission.departmentmodify}
                        <button type="button" class="btn btn-success btn-xs" href="/admin/permission/departmentmodify" data-rel="ajax"><i class="glyphicon glyphicon-th-list"></i> 新增部门</button>
                    {/if}
                    {if $permission.positionmodify}
                        <button type="button" class="btn btn-success btn-xs" href="/admin/permission/positionmodify" data-rel="ajax"><i class="glyphicon glyphicon-user"></i> 新增职位</button>
                    {/if}
                    {if $permission.copyposition}
                        <button type="button" class="btn btn-success btn-xs" href="/admin/permission/copyposition" data-rel="ajax"><i class="glyphicon glyphicon-user"></i> 复制职位</button>
                    {/if}
                </div>
            </div>
            <div class="ibox-content clearfix">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-left">名称</th>
                        <th width="60">状态</th>
                        <th width="320">操作</th>
                    </tr>
                    </thead>
                    <tbody>{$department}</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}