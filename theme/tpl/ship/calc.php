<?php include APP_TPL_DIR . '/inc/dlg_header.php' ?>

<table class="table table-bordered table-hover table-condensed table-striped">
    <tbody>
        <?php
        $point_attr = ['atk', 'torpedo', 'def', 'air_def'];
        $value_attr = ['2', '3', '4', '9'];
        foreach ($result['list'] as $cid => $item) {
            $points = [];
            $values = [];

            for ($j = 0; $j < 4; $j++) {
                $points[$j] = $item['strengthenSupplyExp'][$point_attr[$j]];
                $values[$j] = $item['dismantle'][$value_attr[$j]];
            }
            ?>
            <tr>
                <td class="text-center"><?= $item['title'] ?></td>
                <td class="text-center"><?= $item['count'] ?></td>
                <td class="text-center"><?= join(' / ', $points) ?></td>
                <td class="text-center"><?= join(' / ', $values) ?></td>
            </tr>
            <?php
        }

        $sum_point = [];
        $sum_dismantle = [];

        for ($j = 0; $j < 4; $j++) {
            $sum_point[$j] = $result['sum_point'][$point_attr[$j]];
            $sum_dismantle[$j] = $result['sum_dismantle'][$value_attr[$j]];
            $exceed_point = $result['exceed_point'][$point_attr[$j]];

            if ($exceed_point > 0) {
                $sum_point[$j] .= '<sup style="color:red;">+' . $exceed_point . '</sup>';
            }
            else if ($exceed_point[$j] == 0) {
                $sum_point[$j] .= '<sup style="color:green;">' . $exceed_point . '</sup>';
            }
            else {
                $sum_point[$j] .= '<sup style="color:blue;">' . $exceed_point . '</sup>';
            }
        }
        ?>
        <tr>
            <td class="text-center">合计</td>
            <td class="text-center"></td>
            <td class="text-center"><?= join(' / ', $sum_point) ?></td>
            <td class="text-center"><?= join(' / ', $sum_dismantle) ?></td>
        </tr>
    </tbody>
</table>

<?php include APP_TPL_DIR . '/inc/dlg_footer.php' ?>