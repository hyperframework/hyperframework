<?php
namespace Hyperframework\Web;

use Exception;

abstract class HttpException extends Exception {
    private $statusCode;
    private $statusReasonPhrase;

    /**
     * @param string $message
     * @param int $statusCode
     * @param string $statusReasonPhrase
     * @param Exception $previous
     */
    public function __construct(
        $message, $statusCode, $statusReasonPhrase, $previous = null
    ) {
        if ($message === null) {
            $message = $statusCode . ' ' . $statusReasonPhrase . '.';
        }
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->statusReasonPhrase = $statusReasonPhrase;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->statusCode . ' ' . $this->statusReasonPhrase;
    }

    /**
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getStatusReasonPhrase() {
        return $this->statusReasonPhrase;
    }

    /**
     * @return array
     */
    public function getHttpHeaders() {
        return [];
    }
}
