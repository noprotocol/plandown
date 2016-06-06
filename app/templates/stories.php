<?php

use Sledgehammer\Core\Html;
$total = 0;
?>
<?php render($form); ?>
<table class="table table-condensed table-striped">
    <thead>
        <tr>
            <th>Epic</th>
            <th>Summary</th>
            <th>Points</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stories as $story): $total += $story['points']; ?> 
            <tr>
                <td><?= Html::escape($story['epic']) ?></td>
                <td><?= Html::escape($story['summary']) ?></td>
                <td><?= Html::escape($story['points']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2"><b>Total</b></td>
            <td><b><?= $total ?></b></td>
        </tr>
    </tfoot>
    
</table>
<br>
<br>