{include 'header.tpl'}
<script type="text/javascript">
    if (window.top !== window.self) {
        window.top.location = window.location;
    }
</script>
<body class="gray-bg">
<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <div><h1 class="logo-name" style="font-size: 70px;letter-spacing:0;">抽奖管理系统</h1></div>
        <h3>管理后台登录</h3>
        <form class="m-t" role="form" action="/auth/login" method="POST" id="loginForm">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="用户名" required="" name="username" size="40" style="width: 100%;" />
            </div>
            <div class="form-group">
                <input type="password" class="form-control" placeholder="密码" required="" name="password" size="40" style="width: 100%;" id="password" >
            </div>
            <div class="form-group">
                <div class="input-group">
                    <input type="text" name="check_code" placeholder="输入右侧验证码" class="form-control" required autocomplete="off"/>
                    <span class="input-group-addon" style="padding: 0;"><img src="/auth/authCode" align="middle" id="imgCode" style="height: 32px;"/></span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary block full-width m-b">登 录</button>
            <p class="text-muted text-center"></p>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function (e) {
        var $formEl = $('#loginForm'),$imgEl = $('#imgCode');
        $formEl.submit(function () {
            $formEl.ajaxSubmit({
                dataType: 'json',
                data: { in_ajax: 1 },
                success: function (result) {
                    if(result.code === 500){
                        $imgEl.trigger('click');
                        alert(result.msg);
                    }else{
                        window.location.href = result.url;
                    }
                },
                error: function () {
                    alert('发生错误。');
                }
            });
            return false;
        });
        $imgEl.on('click',this,function (e) {
            var $el = $(this);
            $el.attr('src','/auth/authCode?t=' + Math.random());
        });
    });
</script>
</body>
</html>