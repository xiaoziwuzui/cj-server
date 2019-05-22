{include 'header.tpl'}
<body class="gray-bg">
<div class="wrapper wrapper-content">
    <div class="row">
        <div class="ibox">
            {if $notice}
                <div class="ibox-title">
                    <h5>系统公告</h5>
                </div>
                <div class="ibox-content clearfix" style="margin-bottom: 20px;">
                    {foreach from=$notice item=item}
                    <p class="m-b-sm"><a href="/notice/view?id={$item.id}" data-rel="ajax" data-width="800" data-title="查看公告详情">{$item.title}</a><span class="pull-right">{$item.create_time|date_format:"Y-m-d H:i:s"}</span> </p>
                    {/foreach}
                </div>
            {/if}
            <div class="ibox-title">
                <h5>数据概览</h5>
            </div>
            <div class="ibox-content clearfix" style="margin-bottom: 20px;">
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <tbody>
                    <tr class="text-center">
                        <td>
                            <div class="col-sm-12 p-l-none">
                                <label>车辆数</label>
                                <div class="hr-line-dashed"></div>
                                <div class="text-danger">{intval($total.plate)}</div>
                            </div>
                        </td>
                        <td>
                            <div class="col-sm-12 p-l-none">
                                <label>总订单数</label>
                                <div class="hr-line-dashed"></div>
                                <div class="text-warning">{intval($total.pay_order)}</div>
                            </div>
                        </td>
                        <td>
                            <div class="col-sm-12 p-l-none">
                                <label>总付款金额</label>
                                <div class="hr-line-dashed"></div>
                                <div class="text-danger">¥ {floatval(Service_Public::formatMoney($total.pay_money))}</div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="ibox-title" style="position: relative;">
                <h5>总数据表</h5>
                <div class="_showDate form-group-sm" style="position: absolute;left: 25%;top:7px;">
                    <label>
                    <input type="text" class="form-control _startTime" readonly autocomplete="off" name="begin_date" size="22" value="{$start_date}" placeholder="选择统计数据时间范围"/>
                    </label>
                    <span type="button" class="btn btn-primary btn-primary-outline btn-sm _getData"><i class="wb-refresh"></i> 更新</span>
                </div>
                <div class="_showDate form-group-sm" style="float:right; margin-top: -7px;">
                    <label>
                        <input type="text" class="form-control _dayTime" readonly autocomplete="off" name="begin_date" size="11" value="{$start_day}" placeholder="选择统计数据时间范围"/>
                    </label>
                    <span type="button" class="btn btn-primary btn-primary-outline btn-sm _getDayData"><i class="wb-refresh"></i> 更新</span>
                </div>
            </div>
            <div class="ibox-content clearfix" style="margin-bottom: 20px;">
                <table style="width: 100%;"><tbody><tr>
                        <td style="width: 40%;"><div id="a_c" style="width: 100%;height:350px;"></div></td>
                        <td><div id="a_b" style="width: 100%;height:350px;"></div></td>
                    </tr></tbody></table>

            </div>
            <div class="ibox-title">
                <h5>当日数据概况</h5>
            </div>
            <div class="ibox-content clearfix" style="margin-bottom: 20px;">
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                        <tr>
                            <th class="text-center">付款数</th>
                            <th class="text-center">凌晨订单</th>
                            <th class="text-center">凌晨金额</th>
                            <th class="text-center">白天订单</th>
                            <th class="text-center">白天金额</th>
                            <th class="text-center">总金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td>{intval($day.success_order)}</td>
                            <td>{intval($day.am_total)}</td>
                            <td>{floatval(Service_Public::formatMoney($day.am_money))}</td>
                            <td>{intval($day.pm_total)}</td>
                            <td>{floatval(Service_Public::formatMoney($day.pm_money))}</td>
                            <td>{floatval(Service_Public::formatMoney($day.total_money))}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="ibox-title">
                <h5>当月数据概况</h5>
            </div>
            <div class="ibox-content clearfix" style="margin-bottom: 20px;">
                <table class="table table-striped table-bordered table-hover optimize_table">
                    <thead>
                    <tr>
                        <th class="text-center">付款数</th>
                        <th class="text-center">凌晨订单</th>
                        <th class="text-center">凌晨金额</th>
                        <th class="text-center">白天订单</th>
                        <th class="text-center">白天金额</th>
                        <th class="text-center">总金额</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="text-center">
                        <td>{intval($month.success_order)}</td>
                        <td>{intval($month.am_total)}</td>
                        <td>{floatval(Service_Public::formatMoney($month.am_money))}</td>
                        <td>{intval($month.pm_total)}</td>
                        <td>{floatval(Service_Public::formatMoney($month.pm_money))}</td>
                        <td>{floatval(Service_Public::formatMoney($month.total_money))}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var aChart,bChart;
    $(document).ready(function (e) {
        aChart = echarts.init(document.getElementById('a_c'));
        bChart = echarts.init(document.getElementById('a_b'));
        layui.use(['laydate','layer'],function () {
            layui.laydate.render({
                elem: '._startTime',
                range: true,
                max:'{date('Y-m-d')}',
                done:function (value, date, endDat) {
                    _getData(value);
                }
            });
            layui.laydate.render({
                elem: '._dayTime',
                max:'{date('Y-m-d')}',
                done:function (value, date, endDat) {
                    _getDayData(value);
                }
            });
            _getData();
            _getDayData();
        });
        $('._getData').click(function () {
            _getData();
        });
        $('._getDayData').click(function () {
            _getDayData();
        });
    });
    function _getData(date) {
        var $date = $('._startTime');
        if(typeof date === 'undefined'){
            date=$date.val()
        }
        if(date.length !== 23){
            unit.msg("请选择正确的时间范围!");
            return false;
        }
        unit.api({
            url:'/main/getChartData',
            data:{
                date:date
            },callback:function (result) {
                var aOption,catetory = [],totalData=[],moneyData=[],data,key;
                if(result.data.length === 0){
                    unit.msg('没有相应数据!');
                }
                data = result.data;
                var s = [];
                for(key in data){
                    if(data.hasOwnProperty(key)){
                        s.push(key);
                    }
                }
                s.sort();
                for(var i=0;i< s.length;i++){
                    key = s[i];
                    if(data.hasOwnProperty(key)){
                        catetory.push(key);
                        totalData.push(data[key]['success_order']);
                        moneyData.push(data[key]['total_money']);
                    }
                }
                aOption = {
                    title: {
                        text: '付款数及金额'
                    },
                    tooltip: {
                        trigger: 'axis'
                    },
                    legend: {
                        data:['付款数','总金额']
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    toolbox: {
                        feature: {
                            saveAsImage: { }
                        }
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: catetory
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [
                        {
                            name:'付款数',
                            type:'line',
                            stack: '总量',
                            data:totalData
                        },
                        {
                            name:'总金额',
                            type:'line',
                            stack: '总量',
                            data:moneyData
                        }
                    ]
                };
                aChart.setOption(aOption);
            }
        });
    }
    function _getDayData(date) {
        var $date = $('._dayTime');
        if(typeof date === 'undefined'){
            date=$date.val()
        }
        if(date.length !== 10){
            unit.msg("请选择正确的日期!");
            return false;
        }
        unit.api({
            url:'/main/getChartDay',
            data:{
                day:date
            },callback:function (result) {
                var bOption = { },catetory = [],amData=[],pmData=[],amMoney=[],pmMoney=[],data;
                if(result.data.length === 0){
                    unit.msg('没有相应数据!');
                }
                data = result.data;
                bOption = {
                    title: {
                        text: '每日小时段数据'
                    },
                    tooltip: {
                        trigger: 'axis'
                    },
                    legend: {
                        data:['付款数','付款金额']
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    toolbox: {
                        feature: {
                            saveAsImage: { }
                        }
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: data.category
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [
                        {
                            name:'付款数',
                            type:'line',
                            stack: '总量',
                            data:data.total
                        },
                        {
                            name:'付款金额',
                            type:'line',
                            stack: '总量',
                            data:data.money
                        }
                    ]
                };
                bChart.setOption(bOption);
            }
        });
    }
</script>
{include 'footer.tpl'}