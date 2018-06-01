# Command-Bus-Validator

## Install
`composer require dmt-software/command-bus-validator`

## Usage

### Default usage

By default this middleware uses the StaticMethodLoader of the [Symfony Validator](https://symfony.com/doc/current/components/validator.html) component. 
If you have installed both `doctrine/annotations` and `doctrine/cache`, this default behaviour is extended with the AnnotationLoader.

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

