<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;
use Symfony\Component\Security\Core\SecurityContextInterface;

class BuiltInFunctionsExtension extends AbstractTableBundleExtension
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
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
     * @param int       $offset
     * @param \DateTime $now
     *
     * @return string
     */
    public function today($offset = 0, \DateTime $now = null)
    {
        if ($now === null) {
            $now = new \DateTime();
        } else {
            $now = clone $now;
        }

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
     * @param int       $offset
     * @param \DateTime $now
     *
     * @return string
     */
    public function now($offset = null, \DateTime $now = null)
    {
        if ($now === null) {
            $now = new \DateTime();
        } else {
            $now = clone $now;
        }

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
        return $this->securityContext->getToken()->getUsername();
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
