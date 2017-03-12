<?php
namespace Hyperframework\Web;

use Exception;

class ProxyAuthenticationRequiredException extends HttpException {
    private $authenticationInfo;

    /**
     * @param string $authenticationInfo
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(
        $authenticationInfo, $message = null, $previous = null
    ) {
        parent::__construct(
            $message, 407, 'Proxy Authentication Required', $previous
        );
        $this->authenticationInfo = $authenticationInfo;
    }

    /**
     * @return array
     */
    public function getHttpHeaders() {
        $headers = parent::getHttpHeaders();
        $headers[] = 'Proxy-Authenticate: ' . $this->authenticationInfo;
        return $headers;
    }
}
