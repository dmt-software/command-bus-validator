<?php

namespace DMT\CommandBus\Validator;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use League\Tactician\Middleware;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * Class ValidationMiddleware
 *
 * @package DMT\Validation
 */
class ValidationMiddleware implements Middleware
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * ValidationMiddleware constructor.
     *
     * @param ValidatorInterface|null $validator
     * @throws AnnotationException
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        $this->validator = $validator ?? $this->getDefaultValidator();
    }

    /**
     * @param object $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $violations = $this->validator->validate($command);

        if ($violations->count() > 0) {
            throw new ValidationException(
                sprintf('Invalid command %s given', get_class($command)),
                0,
                null,
                $violations
            );
        }

        return $next($command);
    }

    /**
     * Get a default validator.
     *
     * By default the usage of annotations to validate object is off. To enable annotation configuration install
     * `doctrine/annotations`.
     *
     * @return RecursiveValidator|ValidatorInterface
     * @throws AnnotationException
     */
    protected function getDefaultValidator(): ValidatorInterface
    {
        $loaders = [new StaticMethodLoader()];

        if (class_exists(AnnotationReader::class)) {
            if (class_exists(ArrayCache::class)) {
                $loaders[] = new AnnotationLoader(new CachedReader(new AnnotationReader(), new ArrayCache()));
            } else {
                $loaders[] = new AnnotationLoader(new AnnotationReader());
            }
        }

        return (new ValidatorBuilder())
            ->setMetadataFactory(
                new LazyLoadingMetadataFactory(
                    new LoaderChain($loaders)
                )
            )
            ->getValidator();
    }
}
