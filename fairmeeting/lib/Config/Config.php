<?php
declare(strict_types=1);
namespace OCA\fairmeeting\Config;
use OCA\fairmeeting\AppInfo\Application;
use OCP\IConfig;

class Config {
    public const KEY_fairmeeting_SERVER_URL = 'fairmeeting_server_url';
    public const KEY_JWT_SECRET = 'jwt_secret';
    public const KEY_JWT_APP_ID = 'jwt_app_id';
    public const KEY_JWT_AUDIENCE = 'jwt_audience';
    public const KEY_JWT_ISSUER = 'jwt_issuer';
    public const KEY_JWT_TOKEN = 'jwt_token';
    public const KEY_HELP_LINK = 'help_link';
    public const KEY_DISPLAY_JOIN_USING_THE_fairmeeting_APP = 'display_join_using_the_fairmeeting_app';
    public const KEY_ALL_SHARING_INVITES = 'display_all_sharing_invites';
    public const KEY_OPEN_IN_NEW_TAB = 'open_in_new_tab';
    public const KEY_CALENDAR_INTEGRATION_ENABLED = 'calendar_integration_enabled';
    public const KEY_CALENDAR_MINIMUM_DURATION = 'calendar_minimum_duration';
    public const KEY_CALENDAR_ADD_TO_DESCRIPTION = 'calendar_add_to_description';
    public const KEY_CALENDAR_DESCRIPTION_TEXT = 'calendar_description_text';
    
    public const BOOL_TRUE = '1';
    public const BOOL_FALSE = '0';
    
    /**
     * @var IConfig
     */
    private $config;
    
    public function __construct(IConfig $config) {
        $this->config = $config;
    }
    
    public function fairmeetingServerUrl(): ?string {
        return $this->readString(self::KEY_fairmeeting_SERVER_URL);
    }
    
    public function updatefairmeetingServerUrl(string $serverUrl): void {
        $this->config->setAppValue(Application::APP_ID, self::KEY_fairmeeting_SERVER_URL, $serverUrl);
    }
    
    public function jwtSecret(): ?string {
        return $this->readString(self::KEY_JWT_SECRET);
    }
    
    public function jwtAppId(): ?string {
        return $this->readString(self::KEY_JWT_APP_ID);
    }
    
    public function jwtAudience(): ?string {
        $jwtAudience = $this->readString(self::KEY_JWT_AUDIENCE);
        return empty($jwtAudience) ? $this->jwtAppId() : $jwtAudience;
    }
    
    public function jwtIssuer(): ?string {
        $jwtIssuer = $this->readString(self::KEY_JWT_ISSUER);
        return empty($jwtIssuer) ? $this->jwtAppId() : $jwtIssuer;
    }
    
    public function jwtToken(): ?string {
        return $this->readString(self::KEY_JWT_TOKEN);
    }
    
    public function useManualJwtToken(): bool {
        $token = $this->jwtToken();
        return !empty($token);
    }

    public function hasJwtAuth(): bool {
        return $this->useManualJwtToken() || 
               ($this->jwtSecret() !== null && $this->jwtAppId() !== null);
    }
    
    public function helpLink(): ?string {
        return $this->readString(self::KEY_HELP_LINK);
    }
    
    public function updateHelpLink(string $helpLink): void {
        $this->config->setAppValue(Application::APP_ID, self::KEY_HELP_LINK, $helpLink);
    }
    
    public function displayJoinUsingThefairmeetingApp(): bool {
        // @phpstan-ignore-next-line
        return $this->readBool(self::KEY_DISPLAY_JOIN_USING_THE_fairmeeting_APP, true);
    }
    
    public function displayAllSharingInvites(): bool {
        // @phpstan-ignore-next-line
        return $this->readBool(self::KEY_ALL_SHARING_INVITES, true);
    }
    
    public function openInNewTab(): bool {
        // @phpstan-ignore-next-line
        return $this->readBool(self::KEY_OPEN_IN_NEW_TAB, true);
    }
    
    public function isCalendarIntegrationEnabled(): bool {
        // @phpstan-ignore-next-line
        return $this->readBool(self::KEY_CALENDAR_INTEGRATION_ENABLED, false);
    }
    
    public function updateCalendarIntegrationEnabled(bool $enabled): void {
        $this->config->setAppValue(
            Application::APP_ID, 
            self::KEY_CALENDAR_INTEGRATION_ENABLED, 
            $enabled ? self::BOOL_TRUE : self::BOOL_FALSE
        );
    }
    
    public function getCalendarMinimumDuration(): int {
        $value = $this->config->getAppValue(
            Application::APP_ID,
            self::KEY_CALENDAR_MINIMUM_DURATION,
            '15'
        );
        
        return (int)$value;
    }
    
    public function updateCalendarMinimumDuration(int $minutes): void {
        $this->config->setAppValue(
            Application::APP_ID, 
            self::KEY_CALENDAR_MINIMUM_DURATION, 
            (string)$minutes
        );
    }
    
    public function isCalendarAddToDescriptionEnabled(): bool {
        return $this->readBool(self::KEY_CALENDAR_ADD_TO_DESCRIPTION, false);
    }
    
    public function updateCalendarAddToDescriptionEnabled(bool $enabled): void {
        $this->config->setAppValue(
            Application::APP_ID, 
            self::KEY_CALENDAR_ADD_TO_DESCRIPTION, 
            $enabled ? self::BOOL_TRUE : self::BOOL_FALSE
        );
    }
    
    public function getCalendarDescriptionText(): string {
        $text = $this->readString(self::KEY_CALENDAR_DESCRIPTION_TEXT);
        if ($text === null) {
            return "\n\n--- fairmeeting Video Conference ---\nJoin: {MEETING_URL}\nAutomatically added by fairmeeting integration";
        }
        return $text;
    }
    
    public function updateCalendarDescriptionText(string $text): void {
        $this->config->setAppValue(
            Application::APP_ID, 
            self::KEY_CALENDAR_DESCRIPTION_TEXT, 
            $text
        );
    }
    
    private function readBool(string $key, ?bool $default = null): ?bool {
        $value = $this->config->getAppValue(
            Application::APP_ID,
            $key,
            ''
        );
        
        return $value === '' ? $default : $value === self::BOOL_TRUE;
    }
    
    private function readString(string $key): ?string {
        $value = $this->config->getAppValue(
            Application::APP_ID,
            $key,
            ''
        );
        
        return $value === '' ? null : $value;
    }
}