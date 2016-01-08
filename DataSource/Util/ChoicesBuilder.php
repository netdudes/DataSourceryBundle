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
     * @return array
     *
     * @throws \Exception
     */
    public function build($choicesConfiguration)
    {
        if (is_callable($choicesConfiguration)) {
            return $this->getChoicesFromCallable($choicesConfiguration);
        }

        if (is_array($choicesConfiguration)) {
            if (isset($choicesConfiguration['repository'])) {
                return $this->getChoicesFromRepository($choicesConfiguration);
            }

            return $choicesConfiguration;
        }

        throw new \Exception('No usable configuration was found');
    }

    /**
     * @param array $choicesConfiguration
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getChoicesFromRepository(array $choicesConfiguration)
    {
        if ($choicesConfiguration['repository'] instanceof EntityRepository) {
            $repository = $this->getSpecifiedRepository($choicesConfiguration['repository']);
        } else {
            $repository = $this->entityManager->getRepository($choicesConfiguration['repository']);
        }

        if (isset($choicesConfiguration['field']) && isset($choicesConfiguration['method'])) {
            throw new \Exception('Repository source expects field or method parameter, but not both');
        }

        if (isset($choicesConfiguration['field'])) {
            return $this->getChoicesFromRepositoryForField(
                $repository,
                $choicesConfiguration['field'],
                isset($choicesConfiguration['sort']) ? $choicesConfiguration['sort'] : null
            );
        }
        if (isset($choicesConfiguration['method'])) {
            return $this->getChoicesFromRepositoryWithMethod(
                $repository,
                $choicesConfiguration['method']
            );
        }

        throw new \Exception('Repository source expects field or method parameter');
    }

    /**
     * @deprecated Specifying repository as an object is deprecated and will not be supported since 1.0.0. Specify it by it's name instead.
     *
     * @param EntityRepository $repository
     *
     * @return EntityRepository
     */
    private function getSpecifiedRepository(EntityRepository $repository)
    {
        trigger_error('Specifying repository as an object is deprecated and will not be supported since 1.0.0. Specify it by it\'s name instead.', E_USER_DEPRECATED);

        return $repository;
    }

    /**
     * Get all values available for this data field for consumption by
     * the front-end in order to enable advanced UX functionality.
     *
     * @param EntityRepository $repository
     * @param string           $property
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
     * @return array
     *
     * @throws \Exception
     */
    private function getChoicesFromRepositoryWithMethod(EntityRepository $repository, $method)
    {
        if (!method_exists($repository, $method)) {
            throw new \Exception("Specified repository does not have '$method' method");
        }

        $choices = $repository->$method();
        if (!is_array($choices)) {
            throw new \Exception("Repository method {$method} must return an array of choices");
        }

        return $choices;
    }

    /**
     * @param callable $callable
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getChoicesFromCallable(callable $callable)
    {
        $choices = $callable();
        if (!is_array($choices)) {
            throw new \Exception('The provided choice callback must return an array of choices');
        }

        return $choices;
    }
}
