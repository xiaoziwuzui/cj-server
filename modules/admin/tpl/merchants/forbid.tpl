{include 'header.tpl'}
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-content clearfix">
                <form data-rel="ajax" method="post" action="{$formData.url}" role="form" class="form-horizontal">
                    <div class="form-group col-sm-12">
                        <div>
                            <textarea placeholder="请填写简单的封号原因备查" name="remark" style="width: 100%;" rows="4" class="form-control"></textarea>
                            <p class="help-block help-info"><i class="wb-info-circle"></i> 尽量50字以内</p>
                        </div>
                    </div>
                    <div class="dialog-btn-group">
                        <button type="submit" class="btn btn-danger-outline"> <i class="wb-alert-circle"></i> 立即封号</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}