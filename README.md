# Command-Bus-Validator

[![Build Status](https://travis-ci.org/dmt-software/command-bus-validator.svg?branch=master)](https://travis-ci.org/dmt-software/command-bus-validator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dmt-software/command-bus-validator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dmt-software/command-bus-validator/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/dmt-software/command-bus-validator/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dmt-software/command-bus-validator/?branch=master)

## Install
`composer require dmt-software/command-bus-validator`

## Usage

### Default usage

Configure and adding this middleware to the commandBus:
```php
<?php // src/CommandBus/builder.php
      
use DMT\CommandBus\Validator\ValidationMiddleware;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;

/** @var CommandHandlerMiddleware $commandHandlerMiddleware */
$commandBus = new CommandBus(
  [
      new ValidationMiddleware(),
      $commandHandlerMiddleware 
  ]
);
```
After the CommandBus is added, the commands it receives will be validated when the `handle` method is called:
```php
<?php
 
use DMT\CommandBus\Validator\ValidationException;
use League\Tactician\CommandBus;
 
try {
    /** @var object $command */
    /** @var CommandBus $commandBus */
    $result = $commandBus->handle($command);
} catch (ValidationException $exception) {
    $violations = $exception->getViolations();
    foreach ($violations as $violation) {
        echo $violation->getMessage(); // outputs: the violation message(s)
    }
}
```

### Using custom configured validator 

The validator can also be plugged unto the middleware by providing it to the middleware constructor.

This example uses a FileLoader to determine the constraints for a command.
```php
<?php // src/CommandBus/builder.php
 
use DMT\CommandBus\Validator\ValidationMiddleware;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use Symfony\Component\Validator\ValidatorBuilder;
 
$validator = (new ValidatorBuilder())
    ->addYamlMapping('config/validation.yaml')
    ->getValidator();

/** @var CommandHandlerMiddleware $commandHandlerMiddleware */
$commandBus = new CommandBus(
    [
        new ValidationMiddleware($validator),
        $commandHandlerMiddleware 
    ]
);

```

## Further reading

- [Tactician CommandBus](http://tactician.thephpleague.com/)
- [Validator Loaders](https://symfony.com/doc/current/components/validator/resources.html)

