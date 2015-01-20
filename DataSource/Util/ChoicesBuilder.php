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

    public function build($choicesConfiguration)
    {
        $choices = null;

        if (
            is_array($choicesConfiguration) &&
            isset($choicesConfiguration['repository']) &&
            isset($choicesConfiguration['field'])
        ) {
            if ($choicesConfiguration['repository'] instanceof EntityRepository) {
                $repository = $choicesConfiguration['repository'];
            } else {
                $repository = $this->entityManager->getRepository($choicesConfiguration['repository']);
            }
            $choicesField = $choicesConfiguration['field'];
            $choices = $this->getChoicesFromRepository($repository, $choicesField);
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
     * @return array|null
     */
    private function getChoicesFromRepository(EntityRepository $repository, $property)
    {
        $results = $repository->createQueryBuilder('entity')
            ->select('entity.' . $property)
            ->getQuery()
            ->getArrayResult();
        $choices = [];
        foreach ($results as $key => $value) {
            $choices[$value[$property]] = $value[$property];
        }

        return $choices;
    }
}
