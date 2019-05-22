{include 'header.tpl'}
<div role="alert" class="alert alert-danger" style="width: 100%;max-width: 640px;margin: 20% auto;">
    {$message_content}
    {if $messageType eq 'error'}
        <a href="javascript:;" onclick="history.go(-1);">返回</a>
    {/if}

</div>
{if $jump_url}
    <script type="text/javascript">
        setTimeout(function () {
            location = "{$jump_url}";
        }, 1500);
    </script>
{/if}

{include 'footer.tpl'}
