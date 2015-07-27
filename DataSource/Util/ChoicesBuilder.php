<?php
namespace Netdudes\DataSourceryBundle\DataSource\Util;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ChoicesBuilder
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array|callable $choicesConfiguration
     *
     * @return array|null
     *
     * @throws \Exception
     */
    public function build($choicesConfiguration)
    {
        $choices = null;

        if (
            is_array($choicesConfiguration) &&
            isset($choicesConfiguration['repository'])
        ) {
            if ($choicesConfiguration['repository'] instanceof EntityRepository) {
                $repository = $this->getSpecifiedRepository($choicesConfiguration['repository']);
            } else {
                $repository = $this->entityManager->getRepository($choicesConfiguration['repository']);
            }

            if (isset($choicesConfiguration['field'])) {
                $choices = $this->getChoicesFromRepositoryForField(
                    $repository,
                    $choicesConfiguration['field'],
                    isset($choicesConfiguration['sort']) ? $choicesConfiguration['sort'] : null
                );
            } elseif (isset($choicesConfiguration['method'])) {
                $choices = $this->getChoicesFromRepositoryWithMethod(
                    $repository,
                    $choicesConfiguration['method']
                );
            } else {
                throw new \Exception('Repository source expects field or method parameter');
            }
        } elseif (is_callable($choicesConfiguration)) {
            // Choices is a callable. It will generate the choices array.
            $choices = $this->getChoicesFromCallable($choicesConfiguration);
        } elseif (is_array($choicesConfiguration)) {
            // Just a plain array. Use it as choices
            $choices = $choicesConfiguration;
        }

        return $choices;
    }

    /**
     * @deprecated Repository should be specified by it's name, not as an actual object.
     *
     * @param EntityRepository $repository
     *
     * @return EntityRepository
     */
    private function getSpecifiedRepository(EntityRepository $repository)
    {
        trigger_error('Repository should be specified by it\'s name, not as an actual object.', E_USER_DEPRECATED);
        return $repository;
    }

    /**
     * Get all values available for this data field for consumption by
     * the front-end in order to enable advanced UX functionality.
     *
     * @param EntityRepository $repository
     * @param string           $property
     *
     * @param null|string      $sortOrder
     *
     * @return array
     */
    private function getChoicesFromRepositoryForField(EntityRepository $repository, $property, $sortOrder = null)
    {
        $queryBuilder = $repository->createQueryBuilder('entity')
            ->select('entity.' . $property);

        if (!is_null($sortOrder)) {
            $queryBuilder->orderBy('entity.' . $property, $sortOrder);
        }

        $results = $queryBuilder
            ->getQuery()
            ->getArrayResult();
        $choices = [];
        foreach ($results as $value) {
            $choices[$value[$property]] = $value[$property];
        }

        return $choices;
    }

    /**
     * @param EntityRepository $repository
     * @param string           $method
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function getChoicesFromRepositoryWithMethod(EntityRepository $repository, $method)
    {
        if (!method_exists($repository, $method)) {
            throw new \Exception("Specified repository does not have '$method' method");
        }

        return $repository->$method();
    }

    /**
     * @param callable $callable
     *
     * @return mixed
     *
     * @throws \Exception
     */
    private function getChoicesFromCallable(callable $callable)
    {
        $choices = $callable();
        if (!is_array($choices)) {
            throw new \Exception("Choices callback defined in table configurations must return arrays");
        }

        return $choices;
    }
}
