<ul class="pagination">
    {if $page_info.end gt 0}
        <li><a href="{$page_info.url_pre}1">第一页</a></li>
        {if $page_info.current eq 1}
            <li class="disabled"><span>&laquo;</span></li>
        {else}
            <li><a href="{$page_info.url_pre}{$page_info.prev}">&laquo;</a></li>
        {/if}
        {for $current_page=$page_info.start; $current_page <= $page_info.end; $current_page++}
            {if $page_info.current eq $current_page}
                <li class="active"><span>{$current_page}<span class="sr-only">(current)</span></span></li>
            {else}
                <li><a href="{$page_info.url_pre}{$current_page}">{$current_page}</a></li>
            {/if}
        {/for}

        {if $page_info.end lt $page_info.last}
            <li class="active"><span>...<span class="sr-only">(current)</span></span></li>
        {/if}

        {if $page_info.current eq $page_info.last}
            <li class="disabled"><span>&raquo;</span></li>
        {else}
            <li><a href="{$page_info.url_pre}{$page_info.next}">&raquo;</a></li>
        {/if}
        <li><a href="{$page_info.url_pre}{$page_info.last}" style="border-left: none;">尾页</a></li>
    {/if}
    <li><span>共 {intval($page_info.total)} 条</span></li>
    {if $page_info.total gt 0}
        <li class="form-inline">
            <div class="input-group" style="max-width: 80px;">
                <input id="t_page" type="text" class="form-control" value="{$page_info.current}" onmouseover="this.select()" placeholder="页码" />
                <span class="input-group-addon" onclick="checkNext()" style="cursor: pointer;">跳转</span>
            </div>
        </li>
    {/if}
</ul>
<script type="text/javascript">
    function checkNext() {
        var page = $('.pagination input').val();
        if (page.match(/^\d.*$/)) {
            var url_pre = '{$page_info.url_pre}';
            url_pre = html_decode(url_pre);
            window.location.href = url_pre + page;
        } else {
            alert('请输入正确的页面数！');
        }
    }
    function html_decode(str) {
        var s = "";
        if (str.length === 0) return "";
        s = str.replace(/&amp;/g, "&");
        s = s.replace(/&lt;/g, "<");
        s = s.replace(/&gt;/g, ">");
        s = s.replace(/&nbsp;/g, " ");
        s = s.replace(/&#39;/g, "\'");
        s = s.replace(/&quot;/g, "\"");
        s = s.replace(/<br\/>/g, "\n");
        return s;
    }
</script>