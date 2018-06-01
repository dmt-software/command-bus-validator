<?php

namespace DMT\CommandBus\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * Class ValidationException
 *
 * @package DMT\Validation
 */
class ValidationException extends \RuntimeException
{
    /**
     * @var ConstraintViolationListInterface
     */
    protected $violations;

    /**
     * ValidationException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param ConstraintViolationListInterface|null $violations
     */
    public function __construct(
        string $message = "Invalid command",
        int $code = 0,
        Throwable $previous = null,
        ConstraintViolationListInterface $violations = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->violations = $violations;
    }

    public function getViolations(): ?ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
