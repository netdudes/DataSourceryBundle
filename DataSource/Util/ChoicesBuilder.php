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
            isset($choicesConfiguration['repository']) &&
            isset($choicesConfiguration['field'])
        ) {
            if ($choicesConfiguration['repository'] instanceof EntityRepository) {
                $repository = $this->getSpecifiedRepository($choicesConfiguration['repository']);
            } else {
                $repository = $this->entityManager->getRepository($choicesConfiguration['repository']);
            }
            $choicesField = $choicesConfiguration['field'];
            $choicesSort = isset($choicesConfiguration['sort']) ? $choicesConfiguration['sort'] : null;
            $choices = $this->getChoicesFromRepository($repository, $choicesField, $choicesSort);
        } elseif (is_callable($choicesConfiguration)) {
            // Choices is a callable. It will generate the choices array.
            $choices = $choicesConfiguration();
            if (!is_array($choices)) {
                throw new \Exception("Choices callback defined in table configurations must return arrays");
            }
        } elseif (is_array($choicesConfiguration)) {
            // Just a plain array. Use it as choices
            $choices = $choicesConfiguration;
        }

        return $choices;
    }

    /**
     * Get all values available for this data field for consumption by
     * the front-end in order to enable advanced UX functionality.
     *
     * @param EntityRepository $repository
     * @param                  $property
     *
     * @param null             $sortOrder
     *
     * @return array|null
     */
    private function getChoicesFromRepository(EntityRepository $repository, $property, $sortOrder = null)
    {
        $query = $repository->createQueryBuilder('entity')
            ->select('entity.' . $property);

        if (!is_null($sortOrder)) {
            $query = $query
                ->orderBy('entity.' . $property, $sortOrder);
        }

        $results =
            $query
                ->getQuery()
                ->getArrayResult();
        $choices = [];
        foreach ($results as $key => $value) {
            $choices[$value[$property]] = $value[$property];
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
}
