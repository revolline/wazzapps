<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="row">
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
                    <td><?=$item['dt'];?></td>
                    <td><?=$item['cnt'];?></td>
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
                  <td><?=$item['dt'];?></td>
                  <td><?=$item['USD'];?></td>
                  <td><?=$item['RUB'];?></td>
                  <td><?=$item['IDR'];?></td>
                  <td><?=$item['ZAR'];?></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
