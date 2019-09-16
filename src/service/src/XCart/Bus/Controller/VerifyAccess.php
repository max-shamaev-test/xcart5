<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XCart\Bus\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use XCart\Bus\Auth\EmergencyCodeService;
use XCart\Bus\Auth\TokenService;
use XCart\Bus\Auth\XC5LoginService;
use XCart\SilexAnnotations\Annotations\Router;
use XCart\SilexAnnotations\Annotations\Service;

/**
 * @Service\Service()
 * @Router\Controller()
 */
class VerifyAccess
{
    /**
     * @var TokenService
     */
    private $tokenService;

    /**
     * @var EmergencyCodeService
     */
    private $emergencyCodeService;

    /**
     * @var XC5LoginService
     */
    private $xc5LoginService;

    /**
     * @param TokenService         $tokenService
     * @param EmergencyCodeService $emergencyCodeService
     * @param XC5LoginService      $xc5LoginService
     */
    public function __construct(
        TokenService $tokenService,
        EmergencyCodeService $emergencyCodeService,
        XC5LoginService $xc5LoginService
    ) {
        $this->tokenService = $tokenService;
        $this->emergencyCodeService = $emergencyCodeService;
        $this->xc5LoginService = $xc5LoginService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     *
     * @Router\Route(
     *     @Router\Request(method="GET", uri="/verifyAccess"),
     * )
     */
    public function indexAction(Request $request)
    {
        try {
            $tokenCookie = $request->cookies->get('bus_token');

            $tokenData = $this->tokenService->decodeToken($tokenCookie);
            if (!empty($tokenData['admin_login'])) {
                $xc5Cookie = $request->cookies->get(
                    $this->xc5LoginService->getCookieName()
                );

                if (!$xc5Cookie) {
                    return new JsonResponse(null, 401);
                }
            }

            if ($tokenData && empty($tokenData[TokenService::TOKEN_READ_ONLY])) {
                return new JsonResponse(null, 200);
            }

            $authCode = $request->get('auth_code');
            if ($authCode && $this->emergencyCodeService->checkServiceCode(md5($authCode))) {
                return new JsonResponse(null, 200);
            }

            if ($authCode && $this->emergencyCodeService->checkAuthCode($authCode)) {
                return new JsonResponse(null, 200);
            }

            return new JsonResponse(null, 401);

        } catch (\Exception $e) {
            return new JsonResponse(null, 500);
        }
    }
}
