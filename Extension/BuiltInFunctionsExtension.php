<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;
use Netdudes\DataSourceryBundle\Util\CurrentDateTimeProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
            new TableBundleFunctionExtension('now', $this, 'now'),
            new TableBundleFunctionExtension('startOfDay', $this, 'startOfDay'),
            new TableBundleFunctionExtension('currentUser', $this, 'currentUser'),
            new TableBundleFunctionExtension('random', $this, 'random')
        ];
    }

    /**
     * Gets the current timestamp, with an offset string
     *
     * @param string $offset
     *
     * @return string
     * @throws \Exception
     */
    public function now($offset = null)
    {
        $now = clone $this->dateTimeProvider->get();

        if ($offset) {
            $interval = \DateInterval::createFromDateString($offset);
            $now->add($interval);

            if ($now == $this->dateTimeProvider->get()) {
                // The date didn't change therefore we assume the given offset is not valid
                throw new \Exception($offset . ' is not a valid date/time interval.');
            }
        }

        return $now->format(\DateTime::ISO8601);
    }

    /**
     * Gets a date with the hour 00:00:00
     *
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    public function startOfDay($date = null)
    {
        $now = clone $this->dateTimeProvider->get();

        if ($date) {
            $now = $this->modifyDate($now, $date);
        }

        $now->setTime(0, 0, 0);

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

    /**
     * @param \DateTime $date
     * @param string    $change
     *
     * @return \DateTime
     * @throws \Exception
     */
    private function modifyDate(\DateTime $date, $change)
    {
        try {
            $date->modify($change);
        } catch (\Exception $e) {
            throw new \Exception($change . ' is not a valid date or date offset.');
        }

        return ($date);
    }
}
