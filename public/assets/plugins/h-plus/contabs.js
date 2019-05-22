var conTabs = function () {
	var self = this;
    var $tabsEl = $('.content-tabs'),$pageContentEl = $('.page-tabs-content'),$aEl = $('.J_menuItem');
    /**
	 * 计算元素集合的总宽度
     * @param elements
     * @returns {number}
     */
    self.calSumWidth = function (elements) {
        var width = 0;
        $(elements).each(function () {
            width += $(this).outerWidth(true);
        });
        return width;
    };
    /**
	 * 滚动到指定选项卡
     * @param element
     */
    self.scrollToTab = function (element) {
        var marginLeftVal = self.calSumWidth($(element).prevAll()), marginRightVal = self.calSumWidth($(element).nextAll());
        // 可视区域非tab宽度
        var tabOuterWidth = self.calSumWidth($tabsEl.children().not(".J_menuTabs"));
        //可视区域tab宽度
        var visibleWidth = $tabsEl.outerWidth(true) - tabOuterWidth;
        //实际滚动宽度
        var scrollVal = 0;
        if ($pageContentEl.outerWidth() < visibleWidth) {
            scrollVal = 0;
        } else if (marginRightVal <= (visibleWidth - $(element).outerWidth(true) - $(element).next().outerWidth(true))) {
            if ((visibleWidth - $(element).next().outerWidth(true)) > marginRightVal) {
                scrollVal = marginLeftVal;
                var tabElement = element;
                while ((scrollVal - $(tabElement).outerWidth()) > ($pageContentEl.outerWidth() - visibleWidth)) {
                    scrollVal -= $(tabElement).prev().outerWidth();
                    tabElement = $(tabElement).prev();
                }
            }
        } else if (marginLeftVal > (visibleWidth - $(element).outerWidth(true) - $(element).prev().outerWidth(true))) {
            scrollVal = marginLeftVal - $(element).prev().outerWidth(true);
        }
        $pageContentEl.animate({
            marginLeft: 0 - scrollVal + 'px'
        }, "fast");
    };

    self.menuItem = function (href,name,index) {
        var dataUrl,dataIndex,menuName;
        var flag   = true;
        var $tabEl = $('.J_menuTab');
        if(typeof(href) === 'string'){
            dataUrl = href;
            menuName = name;
            dataIndex = index;
        }else{
            dataUrl = $(this).attr('href');
            dataIndex = $(this).data('index');
            menuName = $.trim($(this).text());
        }
        if (dataUrl === undefined || $.trim(dataUrl).length === 0){
            return false;
        }
        // 选项卡菜单已存在
        $tabEl.each(function () {
            if ($(this).data('id') === dataUrl) {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active').siblings('.J_menuTab').removeClass('active');
                    self.scrollToTab(this);
                    // 显示tab对应的内容区
                    $('.J_mainContent .J_iframe').each(function () {
                        if ($(this).data("id") === dataUrl) {
                            if(dataUrl !== $(this)[0].contentWindow.location.href){
                                $(this).attr('src',dataUrl).show().siblings(".J_iframe").hide();
                            }else{
                                $(this).show().siblings(".J_iframe").hide();
                            }
                            return false
                        }
                    });
                }
                flag = false;
                return false;
            }
        });

        // 选项卡菜单不存在
        if (flag) {
            $tabEl.removeClass('active');
            var str     = '<a href="javascript:;" class="active J_menuTab" data-id="' + dataUrl + '">' + menuName + ' <i class="fa fa-times-circle"></i></a>';
            var lastStr = 'seamless></iframe>';
            // 添加选项卡对应的iframe
            $('.J_mainContent').find('iframe.J_iframe').hide().parents('.J_mainContent').append('<iframe class="J_iframe" name="iframe' + dataIndex + '"  width="100%" height="100%" src="' + dataUrl + '" frameborder="0" data-id="' + dataUrl + '"' + lastStr);
            // 添加选项卡
            $('.J_menuTabs .page-tabs-content').append(str);
            self.scrollToTab($('.J_menuTab.active'));
        }
        return false;
    };

    self.init = function () {
		$(document).ready(function () {
			self.bind();
        });
    };
    self.bind = function () {
		//通过遍历给菜单项加上data-index属性
        $aEl.each(function (index) {
            if (!$(this).attr('data-index')) {
                $(this).attr('data-index', index);
            }
        });
        $aEl.on('click', self.menuItem);
        //关闭其他选项卡
        $('.J_tabCloseOther').on('click', function (){
            $pageContentEl.children("[data-id]").not(":first").not(".active").each(function () {
                $('.J_iframe[data-id="' + $(this).data('id') + '"]').remove();
                $(this).remove();
            });
            $pageContentEl.css("margin-left", "0");
        });
        //滚动到已激活的选项卡
        $('.J_tabShowActive').on('click', function (){
            self.scrollToTab($('.J_menuTab.active'));
        });

        $('.J_menuTabs').on('click', '.J_menuTab', function () {
            if (!$(this).hasClass('active')) {
                var currentId = $(this).data('id');
                // 显示tab对应的内容区
                $('.J_mainContent .J_iframe').each(function () {
                    if ($(this).data('id') === currentId) {
                        $(this).show().siblings('.J_iframe').hide();
                        return false;
                    }
                });
                $(this).addClass('active').siblings('.J_menuTab').removeClass('active');
                self.scrollToTab(this);
            }
        }).on('click', '.J_menuTab i', function () {
        	var $parentTab   = $(this).parents('.J_menuTab');
            var closeTabId   = $parentTab.data('id');
            var currentWidth = $parentTab.width();
			var $frameEl     = $('.J_mainContent .J_iframe');
            var activeId     = null;
            // 当前元素处于活动状态
            if ($parentTab.hasClass('active')) {
                // 当前元素后面有同辈元素，使后面的一个元素处于活动状态
                if ($parentTab.next('.J_menuTab').size()) {
					activeId = $parentTab.next('.J_menuTab:eq(0)').data('id');
                    $parentTab.next('.J_menuTab:eq(0)').addClass('active');
                    $frameEl.each(function () {
                        if ($(this).data('id') === activeId) {
                            $(this).show().siblings('.J_iframe').hide();
                            return false;
                        }
                    });
                    var marginLeftVal = parseInt($pageContentEl.css('margin-left'));
                    if (marginLeftVal < 0) {
                        $pageContentEl.animate({
                            marginLeft: (marginLeftVal + currentWidth) + 'px'
                        }, "fast");
                    }
                    //  移除当前选项卡
                    $parentTab.remove();
                    // 移除tab对应的内容区
                    $frameEl.each(function () {
                        if ($(this).data('id') === closeTabId) {
                            $(this).remove();
                            return false;
                        }
                    });
                }
                // 当前元素后面没有同辈元素，使当前元素的上一个元素处于活动状态
                if ($parentTab.prev('.J_menuTab').size()) {
                    activeId = $parentTab.prev('.J_menuTab:last').data('id');
                    $parentTab.prev('.J_menuTab:last').addClass('active');
                    $frameEl.each(function () {
                        if ($(this).data('id') === activeId) {
                            $(this).show().siblings('.J_iframe').hide();
                            return false;
                        }
                    });
                    //  移除当前选项卡
                    $parentTab.remove();
                    // 移除tab对应的内容区
                    $frameEl.each(function () {
                        if ($(this).data('id') === closeTabId) {
                            $(this).remove();
                            return false;
                        }
                    });
                }
            } else {
                //  移除当前选项卡
                $parentTab.remove();
                // 移除相应tab对应的内容区
                $frameEl.each(function () {
                    if ($(this).data('id') === closeTabId) {
                        $(this).remove();
                        return false;
                    }
                });
                self.scrollToTab($('.J_menuTab.active'));
            }
            return false;
        }).on('dblclick', '.J_menuTab', function () {
            var target = $('.J_iframe[data-id="' + $(this).data('id') + '"]');
            target.prop('src',$(this).data('id'));
        });

        // 左移按扭
        $('.J_tabLeft').on('click', function () {
            var marginLeftVal = Math.abs(parseInt($pageContentEl.css('margin-left')));
            // 可视区域非tab宽度
            var tabOuterWidth = self.calSumWidth($tabsEl.children().not(".J_menuTabs"));
            //可视区域tab宽度
            var visibleWidth  = $tabsEl.outerWidth(true) - tabOuterWidth;
            //实际滚动宽度
            var scrollVal = 0;
            if ($pageContentEl.width() < visibleWidth) {
                return false;
            } else {
                var tabElement = $(".J_menuTab:first");
                var offsetVal = 0;
                while ((offsetVal + $(tabElement).outerWidth(true)) <= marginLeftVal) {
                    //找到离当前tab最近的元素
                    offsetVal += $(tabElement).outerWidth(true);
                    tabElement = $(tabElement).next();
                }
                offsetVal = 0;
                if (self.calSumWidth($(tabElement).prevAll()) > visibleWidth) {
                    while ((offsetVal + $(tabElement).outerWidth(true)) < (visibleWidth) && tabElement.length > 0) {
                        offsetVal += $(tabElement).outerWidth(true);
                        tabElement = $(tabElement).prev();
                    }
                    scrollVal = self.calSumWidth($(tabElement).prevAll());
                }
            }
            $pageContentEl.animate({
                marginLeft: 0 - scrollVal + 'px'
            }, "fast");
        });

        // 右移按扭
        $('.J_tabRight').on('click', function () {
            var marginLeftVal = Math.abs(parseInt($pageContentEl.css('margin-left')));
            // 可视区域非tab宽度
            var tabOuterWidth = self.calSumWidth($tabsEl.children().not(".J_menuTabs"));
            //可视区域tab宽度
            var visibleWidth = $tabsEl.outerWidth(true) - tabOuterWidth;
            //实际滚动宽度
            var scrollVal = 0;
            if ($pageContentEl.width() < visibleWidth) {
                return false;
            } else {
                var tabElement = $(".J_menuTab:first");
                var offsetVal = 0;
                //找到离当前tab最近的元素
                while ((offsetVal + $(tabElement).outerWidth(true)) <= marginLeftVal) {
                    offsetVal += $(tabElement).outerWidth(true);
                    tabElement = $(tabElement).next();
                }
                offsetVal = 0;
                while ((offsetVal + $(tabElement).outerWidth(true)) < (visibleWidth) && tabElement.length > 0) {
                    offsetVal += $(tabElement).outerWidth(true);
                    tabElement = $(tabElement).next();
                }
                scrollVal = self.calSumWidth($(tabElement).prevAll());
                if (scrollVal > 0) {
                    $pageContentEl.animate({
                        marginLeft: 0 - scrollVal + 'px'
                    }, "fast");
                }
            }
        });

        $(".J_tabRefresh").on("click", function (e) {
            var id = $('.J_menuTab.active').data('id');
            var $iframe = $('.J_iframe[data-id="' + id + '"]');
            $iframe.prop('src',id);
        });

        // 关闭全部
        $('.J_tabCloseAll').on('click', function () {
            $pageContentEl.children("[data-id]").not(":first").each(function () {
                $('.J_iframe[data-id="' + $(this).data('id') + '"]').remove();
                $(this).remove();
            });
            $pageContentEl.children("[data-id]:first").each(function () {
                $('.J_iframe[data-id="' + $(this).data('id') + '"]').show();
                $(this).addClass("active");
            });
            $pageContentEl.css("margin-left", "0");
        });
    };
};
var _tabs = new conTabs();
_tabs.init();