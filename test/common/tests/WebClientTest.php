<?php
namespace Hyperframework\Common;

use Hyperframework\Common\Test\TestCase as Base;
use Hyperframework\Common\Test\CustomWebClient;
use Hyperframework\Common\WebClient;

class WebClientTest extends Base {
    const SERVICE_ROOT = 'http://localhost:8000';

    public function testGet() {
        $c = new WebClient;
        $message = $c->get(
            self::SERVICE_ROOT . '/echo_get.php', [
                WebClient::OPT_QUERY_PARAMS => [
                    'message' => 'hi'
                ]
            ]
        );
        $this->assertSame('hi', $message);
    }

    public function testGetByNestedArrayQueryParams() {
        $c = new WebClient;
        $message = $c->get(
            self::SERVICE_ROOT . '/echo_get_json.php', [
                WebClient::OPT_QUERY_PARAMS => [
                    'message' => ['x', 'y']
                ]
            ]
        );
        $this->assertSame('["x","y"]', $message);
        $message = $c->get(
            self::SERVICE_ROOT . '/echo_get_json.php', [
                WebClient::OPT_QUERY_PARAMS => [
                    'message' => ['a' => 'x', 'y']
                ]
            ]
        );
        $this->assertSame('{"a":"x","0":"y"}', $message);
    }

    public function testPost() {
        $c = new WebClient;
        $message = $c->post(
            self::SERVICE_ROOT . '/echo_post.php',
            ['message' => 'hi', 'message2' => 'hi2'],
            [WebClient::OPT_DATA_TYPE => 'multipart/form-data;charset=utf-8']
        );
        $this->assertSame('hi', $message);
    }

    public function testPostJson() {
        $c = new WebClient;
        $message = $c->post(
            self::SERVICE_ROOT . '/echo_post_json.php',
            ['message' => 'hi']
        );
        $this->assertSame('{"message":"hi"}', $message);
    }

    public function testSendAsyncRequests() {
        $c1 = new CustomWebClient;
        $c1->setOptions([
            CURLOPT_URL => self::SERVICE_ROOT . '/echo_get.php',
            WebClient::OPT_QUERY_PARAMS => [
                'message' => 'hi'
            ]
        ]);
        $c2 = new CustomWebClient;
        $c2->setOptions([
            CURLOPT_URL => self::SERVICE_ROOT . '/echo_get.php',
            WebClient::OPT_QUERY_PARAMS => [
                'message' => 'hi'
            ]
        ]);
        $count = 0;
        CustomWebClient::sendAsyncRequests([
            WebClient::OPT_ASYNC_REQUESTS => [$c1, $c2], 
            WebClient::OPT_ASYNC_REQUEST_COMPLETE_CALLBACK => function(
                $client, $response, $error
            ) use (&$count) {
                ++$count;
                $this->assertSame('hi', $response);
            }
        ]);
        $this->assertSame(2, $count);
    }

    public function testAsyncRequestFetchingCallback() {
        $count = 0;
        $isCalled = false;
        $c1 = new CustomWebClient;
        $c1->setOptions([
            CURLOPT_URL => self::SERVICE_ROOT . '/echo_get.php',
            CustomWebClient::OPT_QUERY_PARAMS => [
                'message' => 'hi'
            ],
        ]);
        $c2 = new CustomWebClient;
        $c2->setOptions([
            CURLOPT_URL => self::SERVICE_ROOT . '/echo_get.php',
            CustomWebClient::OPT_QUERY_PARAMS => [
                'message' => 'hi'
            ]
        ]);
        CustomWebClient::sendAsyncRequests([
            CustomWebClient::OPT_ASYNC_REQUEST_COMPLETE_CALLBACK => function(
                $client, $response, $error
            ) use (&$count, $c1, $c2) {
                ++$count;
                $this->assertSame('hi', $response);
            },
            CustomWebClient::OPT_ASYNC_REQUEST_FETCHING_CALLBACK =>
                function() use (&$isCalled, $c1, $c2) {
                    if ($isCalled) {
                        return false;
                    }
                    $isCalled = true;
                    return [$c1, $c2];
                }
        ]);
        $this->assertSame(2, $count);
        $this->assertSame(true, $isCalled);
    }
}
