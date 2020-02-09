<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';

$this->registerJsFile('https://www.gstatic.com/charts/loader.js', ['depends' => [\app\assets\AppAsset::className()]]);
?>
<div class="row">
    <div class="col-12">
        <?php
        $str = "['Дата', 'Кол-во долларов']";
        foreach ($getAmountUsdForDay as $item) {
            $cnt = $item['cnt'];
            $dt = $item['dt'];
            $str .= ",['$dt', $cnt]";
        }

        $script = <<< JS
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        // Create the data table.
        var data = google.visualization.arrayToDataTable([$str]);

        // Set chart options
        var options = {'title':'Диаграмма общей суммы покупок по дням в долларах',
                       'width':900,
                       'height':800};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }

JS;
        $this->registerJs($script, yii\web\View::POS_END);
        ?>
        <div id="chart_div"></div>
    </div>
    <div class="col-sm-6">
        <h4>Вывод общей суммы покупок по дням в долларах</h4>
        <table id="tbl" class="tbl table table-striped" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Дата</th>
                <th>Сумма</th>
            </tr>
            </thead>
            <tbody id="lstLoad">
            <?php
            foreach ($getAmountUsdForDay as $item) {
                ?>
                <tr>
                    <td><?= $item['dt']; ?></td>
                    <td><?= $item['cnt']; ?></td>
                </tr>
                <?php
            }
            ?>

            </tbody>
        </table>
    </div>

    <div class="col-sm-6">
        <h4>Вывод долей дохода от каждой валюты по дням</h4>
        <table id="tbl" class="tbl table table-striped" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Дата</th>
                <th>USD</th>
                <th>RUB</th>
                <th>IDR</th>
                <th>ZAR</th>
            </tr>
            </thead>
            <tbody id="lstLoad">
            <?php
            foreach ($getShareForCurrency as $item) {
                ?>
                <tr>
                    <td><?= $item['dt']; ?></td>
                    <td><?= $item['USD']; ?></td>
                    <td><?= $item['RUB']; ?></td>
                    <td><?= $item['IDR']; ?></td>
                    <td><?= $item['ZAR']; ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
