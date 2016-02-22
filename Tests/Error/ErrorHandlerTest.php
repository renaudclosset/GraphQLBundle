<?php

namespace Tests\Overblog\GraphQLBundle\Error;

use GraphQL\Error;
use GraphQL\Executor\ExecutionResult;
use Overblog\GraphQLBundle\Error\ErrorHandler;
use Overblog\GraphQLBundle\Error\UserError;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ErrorHandler */
    private $errorHandler;

    public function setUp()
    {
        $this->errorHandler = new ErrorHandler();
    }

    public function testMaskErrorWithThrowExceptionSetToFalse()
    {
        $executionResult = new ExecutionResult(
            null,
            [
                new Error('Error without wrapped exception'),
                new Error('Error with wrapped exception', null, new \Exception('My Exception message')),
                new Error('Error with wrapped user error', null, new UserError('My User Error')),
            ]
        );

        $this->errorHandler->handleErrors($executionResult);

        $expected = [
            'data' => null,
            'errors' => [
                [
                    'message' => 'Error without wrapped exception'
                ],
                [
                    'message' => ErrorHandler::DEFAULT_ERROR_MESSAGE
                ],
                [
                    'message' => 'Error with wrapped user error'
                ],
            ]
        ];

        $this->assertEquals($expected, $executionResult->toArray());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage My Exception message
     */
    public function testMaskErrorWithWrappedExceptionAndThrowExceptionSetToTrue()
    {
        $executionResult = new ExecutionResult(
            null,
            [
                new Error('Error with wrapped exception', null, new \Exception('My Exception message')),
            ]
        );

        $this->errorHandler->handleErrors($executionResult, true);
    }

    public function testMaskErrorWithWrappedUserErrorAndThrowExceptionSetToTrue()
    {
        $executionResult = new ExecutionResult(
            null,
            [
                new Error('Error with wrapped user error', null, new UserError('My User Error')),
            ]
        );

        $this->errorHandler->handleErrors($executionResult, true);

        $expected = [
            'data' => null,
            'errors' => [
                [
                    'message' => 'Error with wrapped user error'
                ],
            ]
        ];

        $this->assertEquals($expected, $executionResult->toArray());
    }

    public function testMaskErrorWithoutWrappedExceptionAndThrowExceptionSetToTrue()
    {
        $executionResult = new ExecutionResult(
            null,
            [
                new Error('Error without wrapped exception'),
            ]
        );

        $this->errorHandler->handleErrors($executionResult, true);

        $expected = [
            'data' => null,
            'errors' => [
                [
                    'message' => 'Error without wrapped exception'
                ],
            ]
        ];

        $this->assertEquals($expected, $executionResult->toArray());
    }

    public function testMaskErrorOverrideErrorHandle()
    {
        $executionResult = new ExecutionResult(
            null,
            [
                new Error('Error without wrapped exception'),
            ]
        );

        $this->errorHandler->setErrorHandler(function() {
            return new Error('Override Error');
        });

        $this->errorHandler->handleErrors($executionResult);

        $expected = [
            'data' => null,
            'errors' => [
                [
                    'message' => 'Override Error'
                ],
            ]
        ];

        $this->assertEquals($expected, $executionResult->toArray());
    }
}
