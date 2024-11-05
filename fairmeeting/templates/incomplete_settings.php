<?php

declare(strict_types=1);

use OCA\fairmeeting\AppInfo\Application;
use OCP\IL10N;

/**
 * @var array $_
 * @var IL10N $l
 */

style(Application::APP_ID, 'styles');

?>

<div class="fairmeeting-info-container">
    <h1 class="fairmeeting-info-title">
        <?php p($l->t('Conferences app not yet configured')); ?>
    </h1>
    <p class="fairmeeting-info-text">
        <?php p($l->t('Please contact your administrator to set up the conferences app.')); ?>
    </p>
</div>
