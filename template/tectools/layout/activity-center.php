<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$logTypes = $TecTools->getLogTypes();
$logs = $TecTools->getLogs();

$pages = ceil(count($logs) / 10);

?>

<div class="container">
    <br><br>
    <h2 class="header center">Aktivitet Center</h2>


    <div class="row">
        <?php foreach ($logTypes as $logType): ?>
        <div class="col s6 m4 l3">
            <div onclick="updateSelectedTypes(this)" data-log-type-id="<?= $logType['LogTypeID'] ?>" class="log-type"><?= "{$logType['LogTypeName']} ({$logType['count']}x)" ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col s12">
            <table id="logs">
                <thead>
                <tr>
                    <th>Dato</th>
                    <th>Handling</th>
                    <th>Bruger</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($logs as $log): ?>
                <tr data-log-type-id="<?= $log['FK_LogTypeID'] ?>">
                    <td><?= strftime ('d. %e %B kl. %H:%M:%S', strtotime($log['Created'])) ?></td>
                    <td><?= $log['LogTypeName'] ?></td>
                    <td>
                        <a href="/users/edit?userid=<?= $log['Data']['UserID'] ?>"><?= $log['Data']['UserID'] ?></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="log-pagination mt2 mb2">
                Side

            </div>

            <button class="btn tec-btn mt2" type="button" onclick="history.back()">Tilbage</button>
        </div>
    </div>

    <br><br>
</div>

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/activity-center/activity-center.js"></script>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/activity-center.css">