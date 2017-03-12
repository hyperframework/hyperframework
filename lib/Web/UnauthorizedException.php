<?php
namespace Hyperframework\Web;

use Exception;

class UnauthorizedException extends HttpException {
    private $authenticationInfo;

    /**
     * @param string $authenticationInfo
     * @param string $message
     * @param Exception $previous
     */
    public function __construct(
        $authenticationInfo, $message = null, $previous = null
    ) {
        parent::__construct($message, 401, 'Unauthorized', $previous);
        $this->authenticationInfo = $authenticationInfo;
    }

    /**
     * @return array
     */
    public function getHttpHeaders() {
        $headers = parent::getHttpHeaders();
        $headers[] = 'WWW-Authenticate: ' . $this->authenticationInfo;
        return $headers;
    }
}
