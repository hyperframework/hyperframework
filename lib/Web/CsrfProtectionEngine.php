<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;
use Hyperframework\Common\InvalidOperationException;

class CsrfProtectionEngine {
    private $tokenName;
    private $token;

    /**
     * @param bool $shouldCheckTokenOfUnsafeMethods
     * @return void
     */
    public function run($shouldCheckTokenOfUnsafeMethods = true) {
        $tokenName = $this->getTokenName();
        $token = Request::getCookieParam($tokenName);
        if ($shouldCheckTokenOfUnsafeMethods === false
            || $this->isSafeMethod(Request::getMethod())
        ) {
            if ($token === null) {
                $this->initializeToken();
            }
        } else {
            if ($token === null) {
                throw new ForbiddenException;
            } else {
                $tmp = Request::getBodyParam($tokenName);
                if ($tmp === $token) {
                    return;
                }
                if ($tmp !== null) {
                    $this->initializeToken();
                }
                throw new ForbiddenException;
            }
        }
    }

    /**
     * @return string
     */
    public function getToken() {
        if ($this->token === null) {
            $name = $this->getTokenName();
            $token = Request::getCookieParam($name);
            if ($token !== null) {
                $this->token = $token;
            } else {
                throw new InvalidOperationException(
                    'Csrf protection is not initialized correctly.'
                );
            }
        }
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenName() {
        if ($this->tokenName === null) {
            $this->tokenName = Config::getString(
                'hyperframework.web.csrf_protection.token_name', '_csrf_token'
            );
        }
        return $this->tokenName;
    }

    /**
     * @param string $token
     * @return void
     */
    protected function setToken($token) {
        $this->token = $token;
    }

    /**
     * @return void
     */
    protected function initializeToken() {
        $this->setToken($this->generateToken());
        $cookieDomain = Config::getString(
            'hyperframework.web.csrf_protection.cookie_domain'
        );
        Response::setCookie(
            $this->getTokenName(),
            $this->getToken(),
            ['domain' => $cookieDomain]
        );
    }

    /**
     * @param string $method
     * @return bool
     */
    protected function isSafeMethod($method) {
        return in_array($method, ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * @return string
     */
    protected function generateToken() {
        return sha1(uniqid(mt_rand(), true));
    }
}
