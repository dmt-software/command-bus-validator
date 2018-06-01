<?php

namespace DMT\Test\CommandBus\Validator;

use DMT\CommandBus\Validator\ValidationException;
use DMT\CommandBus\Validator\ValidationMiddleware;
use DMT\Test\CommandBus\Fixtures\AnnotationReaderCommand;
use DMT\Test\CommandBus\Fixtures\ClassMetadataCommand;
use Doctrine\Common\Annotations\AnnotationException;
use PHPUnit\Framework\TestCase;
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
    /**
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testValidCommand()
    {
        $validator = static::getMockForAbstractClass(ValidatorInterface::class);
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

    /**
     * @expectedException \DMT\CommandBus\Validator\ValidationException
     * @expectedExceptionMessageRegExp ~Invalid command .* given~
     *
     * @throws AnnotationException
     */
    public function testLoadClassMetadataValidator()
    {
        $middleware = new ValidationMiddleware();
        $middleware->execute(new ClassMetadataCommand(), 'gettype');
    }

    /**
     * @expectedException \DMT\CommandBus\Validator\ValidationException
     * @expectedExceptionMessageRegExp ~Invalid command .* given~
     *
     * @throws AnnotationException
     */
    public function testAnnotationReaderValidator()
    {
        $middleware = new ValidationMiddleware();
        $middleware->execute(new AnnotationReaderCommand(), 'gettype');
    }

    /**
     * @dataProvider provideConstraintViolations
     *
     * @param ConstraintViolationList $violations
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    public function testInvalidCommand(ConstraintViolationList $violations)
    {
        $validator = static::getMockForAbstractClass(ValidatorInterface::class);
        $validator->expects(static::once())
            ->method('validate')
            ->willReturn($violations);

        try {
            $middleware = new ValidationMiddleware($validator);
            $middleware->execute(
                new \StdClass(),
                function ($command) {
                    return $command;
                }
            );
        } catch (ValidationException $exception) {
            static::assertSame($violations, $exception->getViolations());
        }
    }

    public function provideConstraintViolations(): array
    {
        return [
            [
                new ConstraintViolationList(
                    [new ConstraintViolation('missing property $foo', null, ['foo'], new \StdClass(), 'foo', null)]
                )
            ],
            [
                new ConstraintViolationList(
                    [
                        new ConstraintViolation('missing property $foo', null, ['foo'], new \StdClass(), 'foo', null),
                        new ConstraintViolation('missing property $bar', null, ['bar'], new \StdClass(), 'bar', null),
                        new ConstraintViolation('missing property $baz', null, ['baz'], new \StdClass(), 'baz', null),
                    ]
                )
            ],
        ];
    }
}
