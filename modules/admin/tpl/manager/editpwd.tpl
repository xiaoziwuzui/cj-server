{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <form role="form" action="{$formData.url}" method="POST" data-rel="ajax" class="form-horizontal">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>个人设置</h5>
                </div>
                <div class="ibox-content clearfix">
                    <div class="tabs-container">
                        <ul class="nav nav-tabs _userInfoTabs">
                            <li class="active"><a data-toggle="tab" href="#tab-1" aria-expanded="true">账号设置</a></li>
                        </ul>
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane active">
                                <div class="panel-body">
                                    <div class="form-group col-sm-12">
                                        <label>登录账号：</label>
                                        <div>
                                            <input name="username" value="{$info.username}" readonly class="form-control" placeholder="登录账号"/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label>账号姓名：</label>
                                        <div>
                                            <input name="truename" value="{$info.truename}" class="form-control" placeholder="用户姓名"/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label>原登录密码：</label>
                                        <div>
                                            <input name="old_password" value="" type="password" autocomplete="off" class="form-control" size="35" placeholder="原始密码，为空时不修改"/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label>登录密码：</label>
                                        <div>
                                            <input name="password" value="" type="password" autocomplete="off" class="form-control" size="35" placeholder="登录密码"/>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12" style="margin-bottom: 9px;">
                                        <label>确认密码：</label>
                                        <div>
                                            <input name="re_password" value="" type="password" autocomplete="off" class="form-control" size="35" placeholder="确认密码"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-sm-12 m-t-sm">
                        <button type="submit" class="btn btn-primary clearInput btn-outline"><i class="fa fa-save"></i>  <span class="bold">保存修改</span></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
{include 'footer.tpl'}