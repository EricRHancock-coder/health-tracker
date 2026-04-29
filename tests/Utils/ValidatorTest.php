<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\Validator;

class ValidatorTest extends TestCase {
    public function testValidateRequired() {
        $data = ['email' => 'test@example.com', 'password' => 'secret'];
        
        $this->assertTrue(Validator::validateRequired($data, ['email', 'password']));
        $this->assertFalse(Validator::validateRequired($data, ['email', 'non_existent']));
        $this->assertFalse(Validator::validateRequired(['email' => ' '], ['email']));
    }

    public function testValidateEmail() {
        $this->assertTrue(Validator::validateEmail('valid@example.com'));
        $this->assertFalse(Validator::validateEmail('invalid-email'));
        $this->assertFalse(Validator::validateEmail('test@missingdomain'));
    }

    public function testValidateRole() {
        $this->assertTrue(Validator::validateRole('admin'));
        $this->assertTrue(Validator::validateRole('readwrite'));
        $this->assertTrue(Validator::validateRole('readonly'));
        $this->assertFalse(Validator::validateRole('superuser'));
        $this->assertFalse(Validator::validateRole(''));
    }
}
