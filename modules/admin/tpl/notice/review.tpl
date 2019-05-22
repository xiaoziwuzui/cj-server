{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            <div class="ibox-content clearfix">
                <div class="detail-review">
                    <h1 class="review-title" id="review-title">{$info.title}</h1>
                    <p class="review-intro">发布人:{$info.truename}  发布时间:{$info.create_time|date_format:"Y-m-d H:i:s"}</p>
                    <div class="review-text" id="review-text">{$info.content}</div>
                </div>
            </div>
        </div>
    </div>
</div>
{include 'footer.tpl'}