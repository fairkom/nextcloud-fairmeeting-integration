<?php
declare(strict_types=1);
namespace OCA\fairmeeting\Controller;

use Ahc\Jwt\JWT;
use OCA\fairmeeting\AppInfo\Application;
use OCA\fairmeeting\Config\Config;
use OCA\fairmeeting\Db\RoomMapper;
use OCA\fairmeeting\Service\MeetingUrlResolver;
use OCP\IConfig;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FeaturePolicy;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IUserSession;

class PageController extends AbstractController {
    private RoomMapper $roomMapper;
    private MeetingUrlResolver $urlResolver;
    private IConfig $serverConfig;

    public function __construct(
        string $AppName,
        IRequest $request,
        IUserSession $userSession,
        Config $appConfig,
        RoomMapper $roomMapper,
        MeetingUrlResolver $urlResolver,
        IConfig $serverConfig
    ) {
        parent::__construct($AppName, $request, $userSession, $appConfig);
        $this->roomMapper = $roomMapper;
        $this->urlResolver = $urlResolver;
        $this->serverConfig = $serverConfig;
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
     * Calendar invitation target. Looks up the room, signs a per-user JWT,
     * and 303-redirects to the Jitsi room URL.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PublicPage
     */
    public function join(string $roomName): Response {
        $safeRoomName = preg_replace('/[^a-zA-Z0-9_-]/', '', $roomName) ?? '';
        if ($safeRoomName === '') {
            return new Response(Http::STATUS_NOT_FOUND);
        }

        // Require a stored Room so guessed room names cannot mint a JWT.
        $room = $this->roomMapper->findOneByPublicId($safeRoomName);
        if ($room === null) {
            return new Response(Http::STATUS_NOT_FOUND);
        }

        $base = rtrim($this->urlResolver->resolveFor($room->getCreatorId()), '/');
        $url = $base . '/' . rawurlencode($room->getPublicId());

        try {
            $room->setLastJoinedAt(new \DateTime());
            $this->roomMapper->update($room);
        } catch (\Throwable $e) {
            // best-effort — do not block join on activity-tracking failure
        }

        $user = $this->userSession->getUser();
        $displayName = $user !== null ? $user->getDisplayName() : null;

        // Priority: user's personal token > NC-signed with admin secret > none.
        // No admin-wide shared token is ever sent.
        $jwtToken = null;
        if ($user !== null) {
            $personal = $this->serverConfig->getUserValue(
                $user->getUID(),
                Application::APP_ID,
                'jwt_token',
                ''
            );
            if ($personal !== '') {
                $jwtToken = $personal;
            }
        }
        if ($jwtToken === null && $this->appConfig->jwtSecret() !== null) {
            $jwt = new JWT($this->appConfig->jwtSecret(), 'HS256');
            $jwtToken = $jwt->encode([
                'context' => ['user' => ['name' => $displayName ?? 'Guest']],
                'aud' => $this->appConfig->jwtAudience(),
                'iss' => $this->appConfig->jwtIssuer(),
                'sub' => '*',
                'room' => $room->getPublicId(),
                'exp' => time() + 12 * 60 * 60,
            ]);
        }
        if ($jwtToken !== null) {
            $url .= '?jwt=' . rawurlencode($jwtToken);
        }

        // Jitsi only applies config.* / userInfo.* overrides from the URL hash.
        $hashParams = [];
        if ($displayName !== null) {
            $hashParams[] = 'userInfo.displayName=' . rawurlencode($displayName);
        }
        if ($this->appConfig->isMeetingSkipPrejoinEnabled()) {
            $hashParams[] = 'config.prejoinPageEnabled=false';
            $hashParams[] = 'config.prejoinConfig.enabled=false';
        }
        if ($this->appConfig->isMeetingDisableDeepLinkingEnabled()) {
            $hashParams[] = 'config.disableDeepLinking=true';
        }
        if ($hashParams) {
            $url .= '#' . implode('&', $hashParams);
        }

        return new RedirectResponse($url);
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
                'meeting_skip_prejoin_default' => $this->appConfig->isMeetingSkipPrejoinEnabled(),
                'meeting_disable_deep_linking_default' => $this->appConfig->isMeetingDisableDeepLinkingEnabled(),
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