<?php

use Sledgehammer\Html;
?>
<table class="table table-condensed table-striped">
    <thead>
        <tr>
            <th>Epic</th>
            <th>Summary</th>
            <th>Points</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stories as $story): ?> 
            <tr>
                <td><?= Html::escape($story['epic']) ?></td>
                <td><?= Html::escape($story['summary']) ?></td>
                <td><?= Html::escape($story['points']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>