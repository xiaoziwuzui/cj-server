/**
 * 封装的JS工具类
 * @type {{}}
 */
var unit = {
    NET_SUCCESS:'ok',
    NET_MSG_KEY:'msg',
    before:{},
    HashMap:function () {
        var size = 0;
        var entry = {};
        this.put = function (key , value){
            if(!this.containsKey(key)){size ++ ;}
            entry[key] = value;
        };
        this.get = function (key){
            return this.containsKey(key) ? entry[key] : null;
        };
        this.remove = function(key){
            if( this.containsKey(key)&&(delete entry[key])){size --;}
        };
        this.containsKey = function(key){
            return (key in entry);
        };
        this.getKey = function(){
            var data = [];
            for(var prop in entry){
                data.push(prop);
            }
            return data;
        };
        this.getdata = function(){
            var data = [];
            for(var prop in entry){
                data.push(entry[prop]);
            }
            return data;
        };
        this.containsValue = function (value){
            for(var prop in entry){
                if(entry[prop] === value){return true;}
            }
            return false;
        };
        this.size = function (){
            return size;
        };
        this.clear = function (){
            size = 0;
            entry = {};
        };
    },
    formatTime:function (times,type) {
        if(typeof(type) === 'undefined') type = 'time';
        var date = new Date(times * 1000),string='';
        string += date.getFullYear() + '-';
        string += (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
        string += (date.getDate() < 10 ? '0'+date.getDate() : date.getDate());
        if(type === 'time'){
            string +=  ' ' + (date.getHours() < 10 ? '0'+date.getHours() : date.getHours()) + ':';
            string += (date.getMinutes() < 10 ? '0'+date.getMinutes() : date.getMinutes()) + ':';
            string += (date.getSeconds() < 10 ? '0'+date.getSeconds() : date.getSeconds());
        }
        return string;
    },
    randomString:function (len) {
        len = len || 12;
        var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnoprstuvwxyz1234567890';
        var maxPos = $chars.length;
        var pwd = '';
        for (i = 0; i < len; i++) {
            pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
        }
        return pwd;
    },
    cutStr:function (str, len, symbol) {
        if (typeof(symbol) === 'undefined') {
            symbol = "...";
        }
        len = len || 25;
        var result = str;
        if (str) {
            if (str.length && str.length > len){
                result = str.substr(0, len) + symbol;
            }
        }
        return result;
    },
    error:function (msg) {
        if(typeof(layer) !== 'undefined'){
            layer.msg(msg,{icon:2,time:1500});
        }else{
            alert(msg);
        }
    },
    msg:function (msg,callback) {
        if(typeof(layer) !== 'undefined'){
            if(typeof(msg) === 'object'){
                try {
                    msg = JSON.stringify(msg);
                }catch (e){

                }
            }
            layer.msg(msg,{amin:0,time:1500},function () {
                callback && callback.call(this);
            });
        }else{
            alert(msg);
            callback && callback.call(this);
        }
    },
    getBoxMaxHeight:function () {
        var wh = $(window).height();
        return wh -(20);
    },
    getBoxMaxWidth:function () {
        var ww = $(window).width();
        return ww -(20);
    },
    netWorkCache:{},
    /**
     * 带缓存的网络请求封装方法
     * @param param
     */
    api:function(param){
        var self = this,queryKey = '',currentTimes = new Date().getTime(),responseInfo = null;
        param.type     = param.type || 'GET';
        param.dataType = param.dataType || 'json';
        param.data     = param.data || {};
        param.handle   = param.handle || false;
        param.cache    = param.cache || false;
        param.loading  = param.loading || true;
        param.timeout  = param.timeout || 4000;
        param.expire   = param.expire || (2 * 60 * 1000);
        if(typeof(param.url) === 'undefined' || param.url.length === 0){
            param.callback && param.callback.call(this,{});
            return true;
        }
        if(param.cache === true){
            queryKey = param.url + param.type + param.dataType + JSON.stringify(param.data);
            responseInfo = self.netWorkCache.get(queryKey);
        }
        if(responseInfo === null || responseInfo.expireTime <= currentTimes){
            if(param.loading === true){
                var loadIndex = layer.load(2, {time: 10000});
            }
            var ajaxTemp = $.ajax({
                url  : param.url,
                data : param.data,
                type : param.type,
                dataType:param.dataType,
                timeout:param.timeout,
                success:function(result){
                    if(param.loading === true) {
                        layer.close(loadIndex);
                    }
                    if(param.cache === true) {
                        responseInfo = {
                            data: result,
                            expireTime: currentTimes + param.expire
                        };
                        unit.netWorkCache.put(queryKey, responseInfo);
                    }
                    if(param.handle){
                        param.callback && param.callback.call(this,result);
                    }else{
                        if(result.status === self.NET_SUCCESS){
                            param.callback && param.callback.call(this,result);
                        }else{
                            self.error(result[self.NET_MSG_KEY]);
                            param.error && param.error.call(this,result);
                        }
                    }
                },
                complete: function (XMLHttpRequest, status) {
                    if (status === 'timeout') {
                        ajaxTemp.abort();
                        unit.msg("请求超时,请稍候重试!");
                    }
                },
                error:function(result){
                    if(param.loading === true) {
                        layer.close(loadIndex);
                    }
                    if(param.handle){
                        param.callback && param.callback.call(this,result);
                    }else{
                        try{
                            layer.closeAll();
                        }catch (e){

                        }
                        self.error('服务器异常!');
                    }
                }
            });
        }else{
            param.callback && param.callback.call(this,responseInfo.data);
        }
    },
    parserResponse:function (data,title,width,height) {
        var content = null;
        var area = [width + 'px'];
        if(height !== 'auto'){
            if(height > unit.getBoxMaxHeight()){
                height = unit.getBoxMaxHeight();
            }
            if(height > 0){
                area.push(height + 'px');
            }else{
                area.push(unit.getBoxMaxHeight() + 'px');
            }
        }else{
            area = area[0];
        }
        if(typeof(title) === 'undefined'){
            title = '操作';
        }
        if (typeof(data) === 'string' && data.indexOf('{') === 0) {
            data = eval('(' + data + ')');
        }
        if (typeof(data.data) === 'undefined') {
            if(data.hasOwnProperty('readyState') && data.status === 500){
                unit.dialog({
                    title:data.statusText,
                    type: 1,
                    area: [width + 'px','500px'],
                    shadeClose:true,
                    anim:2,
                    content: data.responseText
                });
                return false;
            }
            if(data.code === 200){
                unit.msg(data.msg,function () {
                    if (typeof(data.url) !== 'undefined' && data.url !== '') {
                        if(data.url === 'back'){
                            window.history.back();
                        }else{
                            window.location.href = data.url;
                        }
                    }
                });
                return false;
            }else{
                content = data;
            }
        } else {
            content = data.data;
        }
        if (data.code === 301) {
            window.location.href = data.url;
            return false;
        }else if(data.code === 500){
            unit.msg(data.msg);
            return false;
        }
        if (typeof(data.url) !== 'undefined' && data.url !== '') {
            unit.msg(data.msg,function () {
                window.location.href = data.url;
            });
        }else{
            unit.dialog({
                title:title,
                type: 1,
                area: area,
                shadeClose:true,
                offset: 'auto',
                anim:2,
                content: content
            });
        }
    },
    /**
     * JS浮点计算加法解决
     */
    add:function(a, b){
        var c, d, e;
        try {
            c = a.toString().split(".")[1].length;
        } catch (f) {
            c = 0;
        }
        try {
            d = b.toString().split(".")[1].length;
        } catch (f) {
            d = 0;
        }
        e = Math.pow(10, Math.max(c, d));
        return (this.mul(a, e) + this.mul(b, e)) / e;
    },
    /**
     * JS浮点计算BUG减法解决
     * @param a
     * @param b
     * @returns {number}
     */
    sub:function (a, b) {
        var c, d, e;
        try {
            c = a.toString().split(".")[1].length;
        } catch (f) {
            c = 0;
        }
        try {
            d = b.toString().split(".")[1].length;
        } catch (f) {
            d = 0;
        }
        e = Math.pow(10, Math.max(c, d));
        return (this.mul(a, e) - this.mul(b, e)) / e;
    },
    /**
     * JS浮点计算乘法解决
     * @param a
     * @param b
     * @returns {number}
     */
    mul:function(a, b) {
        var c = 0,
            d = a.toString(),
            e = b.toString();
        try {
            c += d.split(".")[1].length;
        } catch (f) {}
        try {
            c += e.split(".")[1].length;
        } catch (f) {}
        return Number(d.replace(".", "")) * Number(e.replace(".", "")) / Math.pow(10, c);
    },
    /**
     * JS浮点计算BUG除法解决
     * @param a
     * @param b
     */
    div:function (a, b) {
        var c, d, e = 0,
            f = 0;
        try {
            e = a.toString().split(".")[1].length;
        } catch (g) {}
        try {
            f = b.toString().split(".")[1].length;
        } catch (g) {}
        c = Number(a.toString().replace(".", ""));
        d = Number(b.toString().replace(".", ""));
        return this.mul(c / d, Math.pow(10, f - e));
    },
    dialog:function (jsonConfig) {
        if(typeof(layer) !== 'undefined'){
            // try {
                layer.open(jsonConfig);
            // }catch (e){
                // unit.msg('系统发生错误,请联系技术!');
            // }
        }else{
            alert('弹出组件加载失败');
        }
    },
    confirm:function (text,callback,okText,cancelText) {
        if(typeof(okText) === 'undefined'){
            okText = '确定';
        }
        if(typeof(cancelText) === 'undefined'){
            cancelText = '取消';
        }
        layer.confirm(text, {
            time:0,
            title:'操作确认',
            btn:[okText,cancelText]
        },function () {
            callback && callback.call(this);
        });
    },
    openTab:function (href,title,index) {
        if(typeof(window.parent._tabs) !== 'undefined'){
            window.parent._tabs.menuItem(href,title,index);
        }else{
            window.location.href = href;
        }
    },
    closeTab:function (href,title,index) {
        if(typeof(window.parent._tabs) !== 'undefined'){
            window.parent._tabs.menuItem(href,title,index);
        }else{
            window.location.href = href;
        }
    },
    bind:function () {
        $(document).ready(function () {
            var $bodyEl = $('body');
            $bodyEl.on('click','._previewimg',function (e) {
                var imgurl = $(e.currentTarget).data('href');
                imgurl = imgurl.indexOf('@') > -1 ? imgurl.split('@')[0] : imgurl;
                unit.dialog({
                    title:'图片预览',
                    type:1,
                    scrollbar:true,
                    area:['640px','480px'],
                    content:'<img src="'+imgurl + '" style="width: 100%;" alt="" />'
                });
                e.preventDefault();
            }).on('click','._maxClick',function (evt) {
                var $td = $(evt.currentTarget),$el = $td.find('input._select_btn');
                $el.prop('checked',!$el.prop('checked'));
            }).on('focus','.input-group>.form-control',function (evt) {
                var $el = $(evt.currentTarget);
                $el.parent().addClass('active');
            }).on('blur','.input-group>.form-control',function (evt) {
                var $el = $(evt.currentTarget);
                $el.parent().removeClass('active');
            }).on('click','._checkAll',function(evt){
                var $el = $(evt.currentTarget),$table = $el.parents('table'),value = $el.prop('checked');
                $table.find('._checkItem').prop('checked',value);
            }).on('click','.close-link',function () {
                var o = $(this).closest("div.ibox");
                o.remove()
            }).on('click','.collapse-link',function () {
                var o = $(this).closest("div.ibox"), e = $(this).find("i"), i = o.find("div.ibox-content");
                i.slideToggle(200);
                e.toggleClass("fa-chevron-up").toggleClass("fa-chevron-down");
                o.toggleClass("").toggleClass("border-bottom");
                setTimeout(function () {
                    o.resize();
                    o.find("[id^=map-]").resize()
                }, 50)
            }).on('click','a[data-rel="msg"]',function (event) {
                var $el = $(event.currentTarget),text=$el.data('text');
                unit.dialog({title:"提示",content:text});
            }).on('click','a[data-rel="tab"], button[data-rel="tab"]',function (event) {
                var $el = $(event.currentTarget),nodeName = $el.prop('nodeName'),type='ajax',title='操作',href=$el.prop('href'),dataHref = $el.data('href'),dataUrl = $el.data('url'),dataText = $el.data('text'),dataTitle = $el.data('title'),index = $el.data('index'),textTitle = $el.text();
                if(nodeName !== 'A'){
                    href = $el.attr('href');
                }
                if((typeof(dataHref) !== 'undefined' && dataHref !== '')){
                    href = dataHref;
                }
                if(typeof(dataUrl) !== 'undefined' && dataUrl !== ''){
                    href = dataUrl;
                }
                if(typeof(textTitle) !== 'undefined' && textTitle !== ''){
                    title = textTitle;
                }
                if(typeof(dataTitle) !== 'undefined' && dataTitle !== ''){
                    title = dataTitle;
                }
                if(typeof(index) === 'undefined' || index === ''){
                    index = parseInt(Math.random() * 100);
                }
                event.preventDefault();
                unit.openTab(href,title,index);
            }).on('click','a[data-rel="ajax"], button[data-rel="ajax"]',function (event) {
                var $el = $(event.currentTarget),nodeName = $el.prop('nodeName'),type='ajax',title='操作',href=$el.prop('href'),dataHref = $el.data('href'),dataUrl = $el.data('url'),dataText = $el.data('text'),dataTitle = $el.data('title'),textTitle = $el.text(),width = $el.data('width'),height = $el.data('height'),size = $el.data('size'),bodyWidth = $bodyEl.width(),prompt = $el.data('prompt');
                if(nodeName !== 'A'){
                    href = $el.attr('href');
                }
                if((typeof(dataHref) !== 'undefined' && dataHref !== '')){
                    href = dataHref;
                }
                if(typeof(dataUrl) !== 'undefined' && dataUrl !== ''){
                    href = dataUrl;
                }
                if(typeof(dataText) !== 'undefined' && dataText !== ''){
                    type = 'confirm';
                }
                if(typeof(prompt) !== 'undefined' && prompt !== ''){
                    type = 'prompt';
                }
                if(typeof(textTitle) !== 'undefined' && textTitle !== ''){
                    title = textTitle;
                }
                if(typeof(dataTitle) !== 'undefined' && dataTitle !== ''){
                    title = dataTitle;
                }
                if(typeof(height) === 'undefined'){
                    height = 'auto';
                }
                if(height === '__MAX_HEIGHT__'){
                    height = unit.getBoxMaxHeight() - 30;
                }
                if(typeof(size) !== 'undefined'){
                    size = size.split(',');
                    if(typeof(width) === 'undefined'){
                        width = parseInt(size[0]);
                    }
                    if(height === 'auto' && size.length === 2){
                        if(size[1] === 'auto'){
                            height = 'auto';
                        }else{
                            height = parseInt(size[1]);
                        }
                    }
                }
                if(typeof(width) === 'undefined'){
                    width = parseInt(bodyWidth * 0.6);
                }
                if(width > bodyWidth){
                    width = parseInt(bodyWidth * 0.95);
                }
                if(href.indexOf('?') === -1){
                    href += '?in_ajax=1';
                }else{
                    href += '&in_ajax=1';
                }
                if(type === 'confirm'){
                    unit.confirm(dataText,function () {
                        unit.api({
                            url:href,
                            dataType:'html',
                            handle:true,
                            callback:function (result) {
                                layer.closeAll();
                                unit.parserResponse(result,'[' + title + ']成功',width,height);
                            }
                        });
                    });
                }else if(type === 'ajax'){
                    // if(bodyWidth < 720 && typeof(window.disableAjax) === 'undefined'){
                    //     window.location.href = href;
                    // }
                    unit.api({
                        url:href,
                        dataType:'html',
                        handle:true,
                        callback:function (result) {
                            unit.parserResponse(result,title,width,height);
                        }
                    });
                }else if(type === 'prompt'){
                    layer.prompt({
                        formType: 2,
                        value: '',
                        title: dataText,
                        area: [width + 'px', height + 'px']
                    }, function(value, index){
                        var url = href + '&'+prompt+'='+value;
                        unit.api({
                            url:url,
                            dataType:'html',
                            handle:true,
                            callback:function (result) {
                                layer.close(index);
                                unit.parserResponse(result);
                            }
                        });
                    });
                }
                event.preventDefault();
            }).on('change','label.check-label',function (event) {
                var $el  = $(event.currentTarget),$input = $el.find('input'),type = $input.attr('type'),name=$input.attr('name');
                if($input.prop('checked') === true){
                    //状态失效时要复杂一些
                    if(type === 'radio'){
                        $('input[name="'+name+'"').each(function (index,item) {
                            var $item = $(this),$parent = $item.parent();
                            if($item.prop('checked') === true){
                                $parent.addClass('active');
                            }else{
                                $parent.removeClass('active');
                            }
                        });
                    }else{
                        $el.addClass('active');
                    }
                }else{
                    $el.removeClass('active');
                }
            }).on('submit','form[data-target="grid"]',function (event) {
                var $el  = $(event.currentTarget),action = $el.prop('action'),grid = $el.data('grid'),dataGrid = $(grid).data('zui.datagrid');
                if(typeof(dataGrid) !== 'undefined'){
                    dataGrid.dataSource.remote = function (params) {
                        return {
                            url: action,
                            type: 'GET',
                            dataType: 'json',
                            data: $el.serialize()
                        };
                    };
                    dataGrid.search(unit.randomString(3));
                    event.preventDefault();
                }
            }).on('submit','form[data-rel="ajax"]',function (event) {
                var $el = $(event.currentTarget),beforeName = $el.data('before'),beforeFlag = false,loading = parseInt($el.data('loading'));
                if(beforeName && self.before.hasOwnProperty(beforeName)){
                    beforeFlag = self.before[beforeName].call(this,$el);
                    if(beforeFlag === false){
                        return false;
                    }
                }
                if(loading > 0){
                    var loadIndex = layer.load(loading, {
                        shade: [0.4,'#fff']
                    });
                }
                $el.ajaxSubmit({
                    type: 'post',
                    dataType: 'json',
                    beforeSubmit: function () {
                        try {
                            editor.sync();
                        } catch (e) {

                        }
                    },
                    success: function (data) {
                        if(loading > 0) {
                            layer.close(loadIndex);
                        }
                        if (data.code === 301) {
                            window.location.href = data.url;
                        } else if (data.code !== 200) {
                            unit.parserResponse(data);
                        } else {
                            unit.parserResponse(data);
                        }
                    },
                    error: function (jqXHR, textStatus) {
                        if(loading > 0) {
                            layer.close(loadIndex);
                        }
                        if (textStatus === 'parsererror') {
                            unit.error(jqXHR['responseText']);
                        } else {
                            if (parseInt(jqXHR['status']) !== 200) {
                                unit.dialog({
                                    title:'错误提示',
                                    type: 1,
                                    area: 'auto',
                                    content: '<p style="padding:10px;">发生错误，描述如下：<br /><h1>' + jqXHR['status'] + ' &nbsp; ' + jqXHR['statusText'] + '</h1>' + jqXHR['responseText'] + '</p>'
                                });
                            }
                        }
                    }
                });
                event.preventDefault();
            });
            try{
                $("select._chosen").chosen({ disable_search: true});
            }catch (e){

            }
            try{
                $("._popover").popover({
                    'container':'body',
                    'placement':'top',
                    'trigger':'hover'
                });
            }catch (e){

            }
        });
    },
    getUserOptions:function (key) {
        if(typeof(userSetting) !== 'undefined' && userSetting.hasOwnProperty(key)){
            var setting = userSetting[key];
            return setting.split("\n");
        }else{
            return [];
        }
    },
    neditor:function (options) {
        return new baidu.editor.ui.Editor($.extend({
            UEDITOR_HOME_URL: "/skin/plugins/ueditor/",
            toolbars: [['undo','redo','bold', 'italic', 'underline','insertimage']],
            serverUrl:"/ueditor/action",
            catchRemoteImageEnable:true,
            sourceEditor:'',
            lang: "zh-cn",
            initialContent:"",
            initialFrameWidth: "100%",
            initialFrameHeight: "600",
            initialStyle:"html{height:100%;}body{ font-size:16px;color:#676a6c;padding:0 10px;}img{ max-width:100%;vertical-align: bottom; }p{ margin:0px; }",
            wordCount:false,
            maximumWords:90000,
            enterTag:'p',
            wordCountMsg:'当前已输入 {#count} 个字',
            elementPathEnabled:false,
            enableAutoSave: false,
            imageScaleEnabled:false,
            autoHeightEnabled:true,
            autoFloatEnabled:true,
            minWord:800,
            imagePopup: false,
            charset:"utf-8",
            zIndex: "99",
            iframeCssUrl: "/assets/plugins/neditor/themes/notadd/css/init.css?v=0.2",
            pageBreakTag:"_page_break_tag_"
        },options));
    },
    renderGrid:function (el,options) {
        var height = 600;
        if(typeof(options.states.pager.recPerPage) !== 'undefined'){
            height = (options.states.pager.recPerPage + 1) * 45;
        }
        if(typeof(options.showFooter) !== 'undefined' && options.showFooter === true){
            height += 45;
        }
        return $(el).datagrid($.extend({
            hoverRow:false,
            rowDefaultHeight:44,
            headerHeight:44,
            hoverCol:false,
            showRowIndex:false,
            sortable:true,
            height:'auto',
            disableScroll:true,
            dataSource: {
                cols:options.cols,
                remote: function(params) {
                    var data = { };
                    if(typeof(options.search) !== 'undefined'){
                        data = $.extend(options.search,params);
                    }else{
                        data = params;
                    }
                    return {
                        url: options.url,
                        type: 'GET',
                        dataType: 'json',
                        data:data
                    };
                }
            },
            valueOperator: {
                map: {
                    getter: function(dataValue, cell, dataGrid) {
                        if(typeof(dataGrid.options['fieldMap']) !== 'undefined' && dataGrid.options.fieldMap[cell.config.name + 'Map'] !== 'undefined'){
                            return dataGrid.options.fieldMap[cell.config.name + 'Map'][dataValue];
                        }else{
                            return dataValue;
                        }
                    }
                },
                title: {
                    getter: function(dataValue, cell, dataGrid) {
                        return '<p title="'+dataValue+'" class="am-inline over">'+dataValue+'</p>';
                    }
                },
                time: {
                    getter: function(dataValue, cell, dataGrid) {
                        if(dataValue <= 0){
                            return '';
                        }
                        dataValue = unit.formatTime(dataValue);
                        return '<span title="'+dataValue+'"'+(typeof(cell.config.over) !== 'undefined' ? ' class="over"' : '')+'>'+dataValue+'</span>';
                    }
                },
                money: {
                    getter: function(dataValue, cell, dataGrid) {
                        return parseFloat(dataValue / 100);
                    }
                },
                date: {
                    getter: function(dataValue, cell, dataGrid) {
                        dataValue = unit.formatTime(dataValue,'date');
                        return '<span title="'+dataValue+'"'+(typeof(cell.config.over) !== 'undefined' ? ' class="over"' : '')+'>'+dataValue+'</span>';
                    }
                }
            }
        },options));
    },
    init:function () {
        var self = this;
        self.netWorkCache  = new self.HashMap();
        self.bind();
        /**
         * 扩展一个高级弹出框类
         */
        layer.promptSelect = function (options, callback) {
            options = options || {};
            if(typeof(options) === "function"){
                callback = options;
            }
            var input,content;
            var width = parseInt(options.area[0]) - 40;
            var height = parseInt(options.area[1]) - 160;
            if(typeof(options.sel) !== 'undefined'){
                content = '<select class="_quickSelect form-control" title="" style="width:'+width+'px; margin-bottom: 5px;">';
                for (var i=0;i<options.sel.length;i++){
                    content += '<option value="'+options.sel[i]+'">'+options.sel[i]+'</option>';
                }
                content += '</select>';
            }

            if(options.formType === 2){
                content += '<textarea class="layui-layer-input" style="width:'+width+'px;height:'+height+'px;">' + (options.value || "") + "</textarea>";
            }else{
                content += '<input style="width:'+width+'px;" type="' + (1 === options.formType ? "password" : "text") + '" class="layui-layer-input" value="' + (options.value || "") + '">';
            }
            return layer.open($.extend({
                btn: ["&#x786E;&#x5B9A;", "&#x53D6;&#x6D88;"],
                content: content,
                skin: "layui-layer-prompt",
                success: function (a) {
                    if(typeof(options.sel) !== 'undefined') {
                        var select = a.find('._quickSelect');
                        select.on('change',this,function (a) {
                            input.val($(a.currentTarget).val());
                        });
                    }
                    input = a.find(".layui-layer-input");
                    input.focus();
                },
                yes: function (b) {
                    var e = input.val();
                    if(e === ""){
                        input.focus();
                    }else{
                        if(e.length > (options.maxlength || 500)){
                            layer.tips("&#x6700;&#x591A;&#x8F93;&#x5165;" + (options.maxlength || 500) + "&#x4E2A;&#x5B57;&#x6570;", input, {tips: 1});
                        }else{
                            callback && callback(e, b, input);
                        }
                    }
                }
            }, options))
        };
        //调整手机界面显示
        (function() {
            if (typeof WeixinJSBridge == "object" && typeof WeixinJSBridge.invoke == "function") {
                handleFontSize();
            } else {
                document.addEventListener("WeixinJSBridgeReady", handleFontSize, false);
            }
            function handleFontSize() {
                // 设置网页字体为默认大小
                WeixinJSBridge.invoke('setFontSizeCallback', { 'fontSize' : 0 });
                // 重写设置网页字体大小的事件
                WeixinJSBridge.on('menu:setfont', function() {
                    WeixinJSBridge.invoke('setFontSizeCallback', { 'fontSize' : 0 });
                });
            }
        })();
        //提示用户关注公众号
        if(typeof subscribeTips !== 'undefined' && subscribeTips.length > 0){
            layer.open({
                type: 1,
                title: false,
                btn: false,
                closeBtn: 0,
                area:'100%',
                offset: 'l',
                skin: 'layui-layer-nobg',
                shadeClose: false,
                scrollbar: false,
                content: subscribeTips
            });
        }
    }
};