<?php

namespace QafooLabs\Refactoring\Domain\Model;

class MethodSignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function whenCreateMethodSignatureWithDefaults_ThenIsPrivateAndNotStatic()
    {
        $method = new MethodSignature("foo");

        $this->assertTrue($method->isPrivate());
        $this->assertFalse($method->isStatic());
    }

    /**
     * @test
     */
    public function whenCreateMethodSignatureWithInvalidVisibility_ThenThrowException()
    {
        $this->setExpectedException("InvalidArgumentException");

        $method = new MethodSignature("foo", MethodSignature::IS_PRIVATE | MethodSignature::IS_PUBLIC);
    }

    /**
     * @test
     */
    public function whenCreateMethodSignatureWithStaticOnly_ThenAssumePrivateVisibility()
    {
        $method = new MethodSignature("foo", MethodSignature::IS_STATIC);

        $this->assertTrue($method->isPrivate());
        $this->assertTrue($method->isStatic());
    }
}
