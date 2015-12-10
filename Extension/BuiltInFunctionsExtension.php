<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;
use Netdudes\DataSourceryBundle\Util\CurrentDateTimeProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BuiltInFunctionsExtension extends AbstractTableBundleExtension
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var CurrentDateTimeProvider
     */
    private $dateTimeProvider;

    /**
     * @param TokenStorageInterface   $tokenStorage
     * @param CurrentDateTimeProvider $dateTimeProvider
     */
    public function __construct(TokenStorageInterface $tokenStorage, CurrentDateTimeProvider $dateTimeProvider)
    {
        $this->tokenStorage = $tokenStorage;
        $this->dateTimeProvider = $dateTimeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'table_bundle_extension_built_in';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TableBundleFunctionExtension('today', $this, 'today'),
            new TableBundleFunctionExtension('now', $this, 'now'),
            new TableBundleFunctionExtension('currentUser', $this, 'currentUser'),
            new TableBundleFunctionExtension('random', $this, 'random')
        ];
    }

    /**
     * Gets the current date, with an offset (positive or negative), in days
     *
     * @param int $offset
     *
     * @return string
     */
    public function today($offset = 0)
    {
        $now = clone $this->dateTimeProvider->get();

        $offset = intval($offset, 10);
        $invert = $offset < 0 ? 1 : 0;
        $offset = abs($offset);
        $now->setTime(0, 0, 0);
        $offset = new \DateInterval('P' . $offset . 'D');
        $offset->invert = $invert;
        $now->add($offset);

        return $now->format(\DateTime::ISO8601);
    }

    /**
     * Gets the current timestamp, with an offset string
     *
     * @param int $offset
     *
     * @return string
     */
    public function now($offset = null)
    {
        $now = clone $this->dateTimeProvider->get();

        if ($offset) {
            $offset = \DateInterval::createFromDateString($offset);
            $now->add($offset);
        }

        return $now->format(\DateTime::ISO8601);
    }

    /**
     * Gets the current users' username
     *
     * @return string
     */
    public function currentUser()
    {
        return $this->tokenStorage->getToken()->getUsername();
    }

    /**
     * Gets a random value between $min and $max
     *
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    public function random($min = 0, $max = 10)
    {
        return rand($min, $max);
    }
}
