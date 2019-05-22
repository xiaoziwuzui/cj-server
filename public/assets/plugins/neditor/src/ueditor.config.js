(function () {
    var URL = window.UEDITOR_HOME_URL || getUEBasePath();
    window.UEDITOR_CONFIG = {
        UEDITOR_HOME_URL: URL,
		serverUrl: "/ueditor/action",
		toolbars: [[
            'fullscreen', 'source', '|', 'undo', 'redo', '|',
            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
            'directionalityltr', 'directionalityrtl', 'indent', '|',
            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
            'print', 'preview', 'searchreplace', 'drafts', 'help'
        ]]
		,enableAutoSave: false
        ,saveInterval: 100
		,autoSyncData:false
		,emotionLocalization:true
		,retainOnlyLabelPasted: false
        ,elementPathEnabled : false
        ,wordCount:false
        ,autoHeightEnabled:false
        ,scaleEnabled:false
        ,autoFloatEnabled:false
        ,allowDivTransToP:false
		,enterTag:'br'
 		,xssFilterRules: true
		,inputXssFilter: true
		,outputXssFilter: true
		,blackList:{a:1}
		,whitList: {
			abbr:   ['title'],
			address: [],
			area:   ['shape', 'coords', 'href', 'alt'],
			article: [],
			aside:  [],
			audio:  ['autoplay', 'controls', 'loop', 'preload', 'src'],
			b:      [],
			bdi:    ['dir'],
			bdo:    ['dir'],
			big:    [],
			blockquote: ['cite'],
			br:     [],
			caption: [],
			center: [],
			cite:   [],
			code:   [],
			col:    ['align', 'valign', 'span', 'width'],
			colgroup: ['align', 'valign', 'span', 'width'],
			dd:     [],
			del:    ['datetime'],
			details: ['open'],
			div:    ['id'],
			dl:     [],
			dt:     [],
			em:     [],
			header: [],
			hr:     [],
			i:      [],
			img:    ['src', 'alt', 'title', 'width', 'height', 'id', '_src', 'loadingclass'],
			ins:    ['datetime'],
			mark:   [],
			nav:    [],
			ol:     [],
			p:      [],
			pre:    [],
			s:      [],
			section:[],
			small:  [],
			span:   [],
			sub:    [],
			sup:    [],
			strong: [],
			table:  ['width', 'border', 'align', 'valign'],
			tbody:  ['align', 'valign'],
			td:     ['width', 'rowspan', 'colspan', 'align', 'valign'],
			tfoot:  ['align', 'valign'],
			th:     ['width', 'rowspan', 'colspan', 'align', 'valign'],
			thead:  ['align', 'valign'],
			tr:     ['rowspan', 'align', 'valign'],
			tt:     [],
			u:      [],
			ul:     [],
			video:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'height', 'width']
		}
    };

    function getUEBasePath(docUrl, confUrl) {

        return getBasePath(docUrl || self.document.URL || self.location.href, confUrl || getConfigFilePath());

    }

    function getConfigFilePath() {

        var configPath = document.getElementsByTagName('script');

        return configPath[ configPath.length - 1 ].src;

    }

    function getBasePath(docUrl, confUrl) {

        var basePath = confUrl;


        if (/^(\/|\\\\)/.test(confUrl)) {

            basePath = /^.+?\w(\/|\\\\)/.exec(docUrl)[0] + confUrl.replace(/^(\/|\\\\)/, '');

        } else if (!/^[a-z]+:/i.test(confUrl)) {

            docUrl = docUrl.split("#")[0].split("?")[0].replace(/[^\\\/]+$/, '');

            basePath = docUrl + "" + confUrl;

        }

        return optimizationPath(basePath);

    }

    function optimizationPath(path) {

        var protocol = /^[a-z]+:\/\//.exec(path)[ 0 ],
            tmp = null,
            res = [];

        path = path.replace(protocol, "").split("?")[0].split("#")[0];

        path = path.replace(/\\/g, '/').split(/\//);

        path[ path.length - 1 ] = "";

        while (path.length) {

            if (( tmp = path.shift() ) === "..") {
                res.pop();
            } else if (tmp !== ".") {
                res.push(tmp);
            }

        }

        return protocol + res.join("/");

    }

    window.UE = {
        getUEBasePath: getUEBasePath
    };

})();
