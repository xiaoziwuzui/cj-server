{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            {include 'page_title.tpl'}
            <div class="ibox-content clearfix">
                <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal">
                    <div class="form-group col-sm-12">
                        <label>公告标题：</label>
                        <div>
                            <input name="title" value="{$info.title}" size="40" class="form-control" placeholder="公告标题"/>
                            <div><span class="help-block m-t-xs"><i class="fa fa-info-circle"></i> 长度10到30个字</span></div>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>类型：</label>
                        <div>
                            {foreach from=$notice_type key=key item=item}
                                <label><input type="radio" name="type" value="{$key}"{if $key eq $info.type} checked="checked"{/if}/> {$item}</label>
                            {/foreach}
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>公告正文：</label>
                        <div>
                            {$content_editor}
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>发布人姓名：</label>
                        <div>
                            <input name="publish_name" value="{$info.publish_name}" size="20" class="form-control" placeholder="发布人姓名"/>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label>状态：</label>
                        <div>
                            {foreach from=$status_type key=key item=item}
                                {if $key neq 9}<label><input type="radio" name="status" value="{$key}"{if $key eq $info.status} checked="checked"{/if}/> {$item}</label>{/if}
                            {/foreach}
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <div>
                            <input type="hidden" name="id" value="{$info.id}"/>
                            <button type="submit" class="btn btn-primary">确认发布</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{$init_editor}
<script type="text/javascript">
    window.UEDITOR_CONFIG.whitList.div = ['style','id'];
    window.UEDITOR_CONFIG.whitList.p = ['style','id'];
</script>
{include 'footer.tpl'}