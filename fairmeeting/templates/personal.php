<?php
declare(strict_types=1);
use OCA\fairmeeting\AppInfo\Application;
/** @var array $_ */
script(Application::APP_ID, 'personal');
?>
<div
    id="fairmeeting-personal"
    data-personal-jwt-token="<?= htmlspecialchars($_['personal_jwt_token']); ?>"
    data-token-service-url="<?= htmlspecialchars($_['jwt_token_service_url']); ?>"
    data-effective-server-url="<?= htmlspecialchars($_['effective_server_url']); ?>"
    data-pro-server-url="<?= htmlspecialchars($_['pro_server_url']); ?>"
    data-pro-group-name="<?= htmlspecialchars($_['pro_group_name']); ?>"
    data-in-pro-group="<?= $_['in_pro_group'] ? '1' : '0'; ?>">
</div>
