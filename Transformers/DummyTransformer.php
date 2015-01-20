<?php
namespace Netdudes\DataSourceryBundle\Transformers;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;

class DummyTransformer implements TransformerInterface
{
    /**
     * The transformer accepts a FieldCollection (a row) and returns it back with
     * arbitrary changes to it.
     *
     * Miscellaneous parameters can be passed through the $parameters array
     *
     * @param array              $record
     * @param DataSourceInterface $dataSource
     *
     * @return array
     *
     */
    public function transform(array $record, DataSourceInterface $dataSource)
    {
        return $record;
    }

    public function getRequiredFieldNames()
    {
        return [];
    }
}
