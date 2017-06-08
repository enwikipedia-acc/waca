<?php

/**
 * Created by PhpStorm.
 * User: Matthew
 * Date: 1/16/16
 * Time: 17:34
 */
class AuthUtilityTest extends PHPUnit_Framework_TestCase
{
    private $auth;

    public function setUp() {
        $this->auth = new AuthUtility();
    }

    public function tearDown() {
        $this->auth = NULL;
    }

    public function testCredentialsTest() {
        $this->assertEquals($this->auth->testCredentials("password","This string doesn't have a colon"), false);
    }

    public function isCredentialVersionLatestTest() {}

    public function encryptPasswordTest() {}

    public function encryptVersion1Test() {}

    public function encryptVersion2Test() {}

    public function verifyVersion2Test() {}
}
