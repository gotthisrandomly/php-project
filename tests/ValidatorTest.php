<?php
require_once __DIR__ . '/../includes/Validator.php';

class ValidatorTest {
    public function testValidateUsername() {
        assertTrue(Validator::validateUsername('validuser') === null);
        assertTrue(Validator::validateUsername('us') !== null);
        assertTrue(Validator::validateUsername('toolongusernameisnotvalid') !== null);
        assertTrue(Validator::validateUsername('invalid user') !== null);
    }

    public function testValidatePassword() {
        assertTrue(Validator::validatePassword('ValidPass1') === null);
        assertTrue(Validator::validatePassword('short') !== null);
        assertTrue(Validator::validatePassword('nouppercase1') !== null);
        assertTrue(Validator::validatePassword('NOLOWERCASE1') !== null);
        assertTrue(Validator::validatePassword('NoNumber') !== null);
    }

    public function testValidateEmail() {
        assertTrue(Validator::validateEmail('valid@email.com') === null);
        assertTrue(Validator::validateEmail('invalid.email') !== null);
        assertTrue(Validator::validateEmail('invalid@email') !== null);
    }

    public function testSanitizeInput() {
        assertEquals('Test', Validator::sanitizeInput(' Test '));
        assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', Validator::sanitizeInput('<script>alert(1)</script>'));
    }

    public function testValidateBet() {
        assertTrue(Validator::validateBet(50, 1, 100) === null);
        assertTrue(Validator::validateBet(0, 1, 100) !== null);
        assertTrue(Validator::validateBet(101, 1, 100) !== null);
        assertTrue(Validator::validateBet('not a number', 1, 100) !== null);
    }
}