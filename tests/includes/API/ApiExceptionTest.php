<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca\Tests\Api;

use PHPUnit\Framework\TestCase;
use Waca\API\ApiException;

class ApiExceptionTest extends TestCase
{
    /** @var  string */
    private $message;
    /** @var ApiException */
    private $ex;

    public function setUp(): void
    {
        $this->message = "This is a test message";

        try {
            throw new ApiException($this->message);
        }
        catch (ApiException $ex) {
            $this->ex = $ex;
        }
    }

    public function testMessage()
    {
        $this->assertEquals($this->message, $this->ex->getMessage());
        $this->assertNotEquals(null, $this->ex->getMessage());
    }
}
