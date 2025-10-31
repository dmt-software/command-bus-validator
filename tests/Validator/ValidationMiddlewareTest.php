<?php

namespace DMT\Test\CommandBus\Validator;

use DMT\CommandBus\Validator\ValidationException;
use DMT\CommandBus\Validator\ValidationMiddleware;
use DMT\Test\CommandBus\Fixtures\AttributeReaderCommand;
use DMT\Test\CommandBus\Fixtures\ClassMetadataCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ValidationMiddlewareTest
 *
 * @package DMT\Validation
 */
class ValidationMiddlewareTest extends TestCase
{
    public function testValidCommand(): void
    {
        /** @var ValidatorInterface $validator */
        $validator = static::getMockBuilder(ValidatorInterface::class)->getMock();
        $validator->expects(static::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $expected = (object)['foo' => 'bar'];

        $middleware = new ValidationMiddleware($validator);
        $result = $middleware->execute(
            $expected,
            function ($command) {
                return $command;
            }
        );

        static::assertSame($expected, $result);
    }

    public function testLoadClassMetadataValidator(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches("~Invalid command .* given~");

        $middleware = new ValidationMiddleware();
        $middleware->execute(new ClassMetadataCommand(), 'gettype');
    }

    public function testAttributeReaderValidator(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches("~Invalid command .* given~");

        $middleware = new ValidationMiddleware();
        $middleware->execute(new AttributeReaderCommand(), 'gettype');
    }

    #[DataProvider(methodName: "provideConstraintViolations")]
    public function testInvalidCommand(ConstraintViolationList $violations): void
    {
        /** @var ValidatorInterface $validator */
        $validator = static::getMockBuilder(ValidatorInterface::class)->getMock();
        $validator->expects(static::once())
            ->method('validate')
            ->willReturn($violations);

        try {
            $middleware = new ValidationMiddleware($validator);
            $middleware->execute(
                new stdClass(),
                function ($command) {
                    return $command;
                }
            );
        } catch (ValidationException $exception) {
            static::assertSame($violations, $exception->getViolations());
        }
    }

    public static function provideConstraintViolations(): array
    {
        return [
            [
                new ConstraintViolationList(
                    [new ConstraintViolation('missing property $foo', null, ['foo'], new stdClass(), 'foo', null)]
                )
            ],
            [
                new ConstraintViolationList(
                    [
                        new ConstraintViolation('missing property $foo', null, ['foo'], new stdClass(), 'foo', null),
                        new ConstraintViolation('missing property $bar', null, ['bar'], new stdClass(), 'bar', null),
                        new ConstraintViolation('missing property $baz', null, ['baz'], new stdClass(), 'baz', null),
                    ]
                )
            ],
        ];
    }
}
