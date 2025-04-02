<?php
declare(strict_types=1);
namespace OCA\fairmeeting\Controller;

use OCA\fairmeeting\Config\Config;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FeaturePolicy;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IUserSession;

class PageController extends AbstractController {
    public function __construct(
        string $AppName,
        IRequest $request,
        IUserSession $userSession,
        Config $appConfig
    ) {
        parent::__construct($AppName, $request, $userSession, $appConfig);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        if (($checkBrowserResult = $this->checkBrowser()) !== null) {
            return $checkBrowserResult;
        }

        return new TemplateResponse('fairmeeting', 'index');
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     */
    public function blank(): TemplateResponse {
        return new TemplateResponse('fairmeeting', 'blank');
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     */
    public function room(string $publicId): TemplateResponse {
        if (($checkBrowserResult = $this->checkBrowser()) !== null) {
            return $checkBrowserResult;
        }

        $loggedIn = $this->userSession->isLoggedIn();
        $renderAs = $loggedIn ? 'user' : 'public';

        // Add JWT token information
        $hasManualJwtToken = $this->appConfig->useManualJwtToken();
        $jwtToken = $hasManualJwtToken ? $this->appConfig->jwtToken() : null;

        $response = new TemplateResponse(
            'fairmeeting',
            'room',
            [
                'loggedIn' => $loggedIn,
                'serverUrl' => $this->appConfig->fairmeetingServerUrl(),
                'helpLink' => $this->appConfig->helpLink(),
                'display_join_using_the_fairmeeting_app' => $this->appConfig->displayJoinUsingThefairmeetingApp(),
                'display_all_sharing_invites' => $this->appConfig->displayAllSharingInvites(),
                'open_in_new_tab' => $this->appConfig->openInNewTab(),
                'has_manual_jwt_token' => $hasManualJwtToken,
                'jwt_token' => $jwtToken,
            ],
            $renderAs
        );

        $this->setPolicies($response);
        return $response;
    }

    private function setPolicies(Response $response): void {
        $serverUrl = $this->appConfig->fairmeetingServerUrl();
        $serverHost = $this->determinefairmeetingHost();

        if ($serverUrl === null || $serverHost === null) {
            return;
        }

        $csp = new ContentSecurityPolicy();
        $csp->addAllowedFrameDomain($serverHost);
        $response->setContentSecurityPolicy($csp);

        $fp = new FeaturePolicy();
        $fp->addAllowedCameraDomain('https://nextcloud.local');
        $fp->addAllowedCameraDomain('https://' . $_SERVER['HTTP_HOST']);
        $fp->addAllowedCameraDomain($serverUrl);
        $fp->addAllowedMicrophoneDomain('https://nextcloud.local');
        $fp->addAllowedMicrophoneDomain('https://' . $_SERVER['HTTP_HOST']);
        $fp->addAllowedMicrophoneDomain($serverUrl);
        $response->setFeaturePolicy($fp);
    }

    private function determinefairmeetingHost(): ?string {
        $serverUrl = $this->appConfig->fairmeetingServerUrl();

        if ($serverUrl === null) {
            return null;
        }

        $urlParts = parse_url($serverUrl);
        // @phpstan-ignore-next-line
        return $urlParts['host'];
    }
}