<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\Response;

class ResponseTest extends TestCase {
    public function testSuccessResponse() {
        $data = ['id' => 1];
        $message = 'Success';
        $status = 200;
        $response = Response::success($data, $message, $status);
        $payload = $response->toArray();

        $this->assertTrue($payload['success']);
        $this->assertEquals($data, $payload['data']);
        $this->assertEquals($message, $payload['message']);
        $this->assertEquals($status, $response->getStatusCode());
    }

    public function testErrorResponse() {
        $message = 'Unauthorized';
        $status = 401;
        $details = ['reason' => 'token expired'];
        $response = Response::error($message, $status, $details);
        $payload = $response->toArray();

        $this->assertFalse($payload['success']);
        $this->assertEquals($message, $payload['error']);
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals($details, $payload['details']);
    }
}
