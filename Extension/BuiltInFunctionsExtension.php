<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Util\CurrentDateTimeProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BuiltInFunctionsExtension implements UqlExtensionInterface
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
    public function getFunctions()
    {
        return [
            new UqlFunction('now', $this, 'now'),
            new UqlFunction('startOfDay', $this, 'startOfDay'),
            new UqlFunction('startOfWeek', $this, 'startOfWeek'),
            new UqlFunction('startOfMonth', $this, 'startOfMonth'),
            new UqlFunction('startOfYear', $this, 'startOfYear'),
            new UqlFunction('currentUser', $this, 'currentUser'),
            new UqlFunction('random', $this, 'random')
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
     * Gets the Monday of the week for the specified date with the hour 00:00:00
     *
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    public function startOfWeek($date = null)
    {
        $now = clone $this->dateTimeProvider->get();

        if ($date) {
            $now = $this->modifyDate($now, $date);
        }

        $year = $now->format('o'); // o = ISO-8601 year number
        $week = $now->format('W'); // W = ISO-8601 week number of year, weeks starting on Monday

        $startOfWeek = $now->setISODate($year, $week);
        $startOfWeek->setTime(0, 0, 0);

        return $startOfWeek->format(\DateTime::ISO8601);
    }

    /**
     * Gets the first day of the month for the specified date with the hour 00:00:00
     *
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    public function startOfMonth($date = null)
    {
        $now = clone $this->dateTimeProvider->get();

        if ($date) {
            $now = $this->modifyDate($now, $date);
        }

        $year = $now->format('Y');
        $month = $now->format('m');

        $startOfMonth = $now->setDate($year, $month, 1);
        $startOfMonth->setTime(0, 0, 0);

        return $startOfMonth->format(\DateTime::ISO8601);
    }

    /**
     * Gets the first day of the year for the specified date with the hour 00:00:00
     *
     * @param string $date
     *
     * @return string
     * @throws \Exception
     */
    public function startOfYear($date = null)
    {
        $now = clone $this->dateTimeProvider->get();

        if ($date) {
            $now = $this->modifyDate($now, $date);
        }

        $year = $now->format('Y');

        $startOfYear = $now->setDate($year, 1, 1);
        $startOfYear->setTime(0, 0, 0);

        return $startOfYear->format(\DateTime::ISO8601);
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
