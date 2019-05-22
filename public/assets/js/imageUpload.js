var imageUpload = function (option) {
    var self = this;
    self.options = {};
    self._Uploader = null;
    self.$el = null;
    self.init = function (option) {
        var elHtml = '';
        if(typeof(WebUploader) === 'undefined'){
            console.error('上传组件缺失');
            return false;
        }
        if(typeof($) === 'undefined'){
            console.error('上传组件需要jQuery支持');
            return false;
        }
        if (!WebUploader.Uploader.support()) {
            alert('Web Uploader 不支持您的浏览器！如果你使用的是IE浏览器，请尝试升级 flash 播放器');
            throw new Error('WebUploader does not support the browser you are using.');
        }
        option = option || {};
        self.options = $.extend({
            el:'#thumb',
            auto:true,
            fileVal:'Filedata',
            inputName:'thumb',
            inputVal:false,
            threads:1,
            uploadType:'image',
            swf:'/assets/plugins/ueditor/third-party/webuploader/Uploader.swf',
            server:'/ueditor/uploadimage',
            pick:'#picker_thumb',
            multiple:false,
            errorAutoRemove:true,
            accept: {
                extensions: 'jpg,jpeg,png,gif',
                mimeTypes: 'image/*'
            }
        },option);
        self.$el = $(self.options.el);
        if(self.$el.size() <= 0){
            console.error('上传组件需要一个父元素');
            return false;
        }
        if(self.options.inputVal === false){
            elHtml = '<div id="uploader_'+self.options.inputName+'" class="diy-webuploader"><div class="uploader-list"></div><div id="picker_'+self.options.inputName+'">选择文件</div>'+(self.options.auto ? '' : '<span class="btn btn-success btn-sm _startUpload"><i class="fa fa-upload"></i>开始上传</span>')+'</div>';
            self.$el.html(elHtml);
        }
        $('#uploader_'+self.options.inputName).on('click','._moveImg',function (e) {
            if(confirm('确定要移除这张图片吗?')){
                var $el = $(e.currentTarget),$item = $el.parent();
                if(typeof(window['removeImg' + self.options.inputName]) === 'function'){
                    window['removeImg' + self.options.inputName].call(this,$item.find('input').val());
                }
                $item.remove();
                self.$el.find('input[type="file"]').val('');
            }
        });
        self._Uploader = WebUploader.create(self.options);
        self._Uploader.onFileQueued = function (file) {
            var html = '';
            html = '<div id="' + file.id + '" class="item"><span class="_moveImg" title="移除图片"><i class="wb-close"></i></span><div class="imgWrap'+(self.options.uploadType !== 'image' ? ' fileWrap' : '')+'"></div></div>';
            if(self.options.multiple === true){
                $(html).appendTo(self.$el.find('.uploader-list'));
            }else{
                self.$el.find('.uploader-list').html( html );
            }
            if(self.options.uploadType !== 'image'){
                return '';
            }
            self.$el.find('input[type="file"]').val('');
            var $wrap = $('#' + file.id).find('.imgWrap');
            self._Uploader.makeThumb(file, function (error, src) {
                if (error || !src) {
                    $wrap.text('无法预览');
                } else {
                    var $img = $('<table class="imgTable"><tbody><tr><td><img src="' + src + '"></td></tr></tbody></table>');
                    $wrap.empty().append($img);
                    $img.on('error', function () {
                        $wrap.text('无法预览');
                    });
                }
            }, 120, 120);
        };
        self._Uploader.onError = function (code) {
            var msg = {
                'F_DUPLICATE':'文件重复选择'
            };
            if(msg.hasOwnProperty(code)){
                unit.error('错误信息: ' + msg[code]);
            }
        };
        self._Uploader.on('beforeFileQueued',function (file) {
            if(typeof(self.options.fileNumLimit) !== "undefined" && self.$el.find('.uploader-list').find('.item').length >= self.options.fileNumLimit){
                unit.error('最多只允许上传' + self.options.fileNumLimit + '个文件');
                return false;
            }
        });
        self._Uploader.on('uploadBeforeSend',function (block,data,headers) {
            data = {};
            if(self.options.formData){
                data = self.options.formData;
            }
        });
        self._Uploader.on( 'uploadProgress', function( file, percentage ) {
            var $li = $( '#'+file.id ),$percent = $li.find('.progress span');
            if ( !$percent.length ) {
                $percent = $('<p class="progress"><span></span></p>').appendTo( $li ).find('span');
            }
            $percent.show().css( 'width', percentage * 100 + '%' );
        });
        self._Uploader.on( 'uploadSuccess', function( file ,response) {
            var $el = $( '#'+file.id ),re_data = '',re_url = '';
            if(typeof(response) === 'string'){
                try{
                    response = $.parseJSON(response);
                }catch (e){
                    response = {
                        state:'解析内容失败'
                    };
                }
            }
            self.$el.find('input[type="file"]').val('');
            if(response.state === 'SUCCESS'){
                if(response.url.indexOf('http') >= 0){
                    re_data = response.hash;
                    re_url  = response.url;
                }else{
                    re_data = response.url;
                    re_url  = '/' + response.url;
                }
                $el.append('<input type="hidden" value="'+re_data+'" name="'+self.options.inputName +(self.options.multiple === false ? '' : '[]') + '" />');
                if(self.options.uploadType === 'image'){
                    $el.find('.imgWrap').find('img').attr('src', re_url);
                }else{
                    $el.find('.imgWrap').text(re_url);
                }
            }else{
                if(self.options.errorAutoRemove === true){
                    unit.error(response.state);
                    $el.remove();
                }else{
                    $el.find('.imgWrap').append('<p style="color:#FFF;font-weight:300;font-size:12px;position: absolute;left:5px;right:5px;background:rgba(0,0,0,0.5);text-align:center;bottom:5px;margin: 0;">'+response.state+'</p>');
                    setTimeout(function () {
                        $el.slideUp('fast',function () {
                            $el.remove();
                        });
                    },4000);
                }
            }
        });
        self._Uploader.on( 'uploadError', function( file ) {
            var $li = $( '#'+file.id ),$error = $li.find('div.error');
            self.$el.find('input[type="file"]').val('');
            if ( !$error.length ) {
                $error = $('<div class="error"></div>').appendTo( $li );
            }
            $li.find('.imgWrap').append('<p style="color:#FFF;font-weight:300;font-size:12px;position: absolute;left:5px;right:5px;background:rgba(0,0,0,0.5);text-align:center;bottom:5px;margin: 0;">上传失败</p>');
            setTimeout(function () {
                $li.slideUp('fast',function () {
                    $li.remove();
                });
            },4000);
        });
        self._Uploader.on( 'uploadComplete', function( file ) {
            var $el = $( '#'+file.id );
            $el.find('.progress').remove();
            $el.find('.state').text('上传完成');
        });
    };
    self.init(option);
};