<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineMongoDBAdminBundle\Util;

use Doctrine\ODM\MongoDB\DocumentManager;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Security\Handler\AclSecurityHandlerInterface;
use Sonata\AdminBundle\Util\ObjectAclManipulator as BaseObjectAclManipulator;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.x.
 */
class ObjectAclManipulator extends BaseObjectAclManipulator
{
    /**
     * @var ManagerRegistry|null
     */
    private $registry;

    // NEXT_MAJOR: Make "$registry" mandatory and remove the "if" block
    public function __construct(?ManagerRegistry $registry = null)
    {
        if (null === $registry) {
            @trigger_error(sprintf(
                'Not passing a "%s" instance as argument 1 for "%s()" is deprecated since'
                .' sonata-project/doctrine-mongodb-admin-bundle 3.4 and will throw a %s error in 4.0.',
                ManagerRegistry::class,
                __METHOD__,
                \TypeError::class
            ), E_USER_DEPRECATED);
        }

        $this->registry = $registry;
    }

    public function batchConfigureAcls(OutputInterface $output, AdminInterface $admin, ?UserSecurityIdentity $securityIdentity = null)
    {
        $securityHandler = $admin->getSecurityHandler();
        if (!$securityHandler instanceof AclSecurityHandlerInterface) {
            $output->writeln('Admin class is not configured to use ACL : <info>ignoring</info>');

            return;
        }

        $output->writeln(sprintf(' > generate ACLs for %s', $admin->getCode()));
        $objectOwnersMsg = null === $securityIdentity ? '' : ' and set the object owner';

        // NEXT_MAJOR: Remove the completely the "else" part and the "if" check.
        if (null !== $this->registry) {
            $om = $this->registry->getManagerForClass($admin->getClass());
            \assert($om instanceof DocumentManager);
        } else {
            $modelManager = $admin->getModelManager();
            \assert($modelManager instanceof ModelManager);
            $om = $modelManager->getDocumentManager($admin->getClass());
        }

        $qb = $om->createQueryBuilder($admin->getClass());

        $count = 0;
        $countUpdated = 0;
        $countAdded = 0;

        try {
            $batchSize = 20;
            $batchSizeOutput = 200;
            $objectIds = [];
            $objectIdIterator = new \ArrayIterator();

            foreach ($qb->getQuery()->getIterator() as $row) {
                $objectIds[] = ObjectIdentity::fromDomainObject($row);
                $objectIdIterator = new \ArrayIterator($objectIds);

                // detach from Doctrine, so that it can be Garbage-Collected immediately
                $om->detach($row);

                ++$count;

                if (0 === ($count % $batchSize)) {
                    [$batchAdded, $batchUpdated] = $this->configureAcls($output, $admin, $objectIdIterator, $securityIdentity);
                    $countAdded += $batchAdded;
                    $countUpdated += $batchUpdated;
                    $objectIds = [];
                }

                if (0 === ($count % $batchSizeOutput)) {
                    $output->writeln(sprintf(
                        '   - generated class ACEs%s for %s objects (added %s, updated %s)',
                        $objectOwnersMsg,
                        $count,
                        $countAdded,
                        $countUpdated
                    ));
                }
            }

            if (\count($objectIds) > 0) {
                [$batchAdded, $batchUpdated] = $this->configureAcls($output, $admin, $objectIdIterator, $securityIdentity);
                $countAdded += $batchAdded;
                $countUpdated += $batchUpdated;
            }
        } catch (\BadMethodCallException $e) {
            throw new ModelManagerException('', 0, $e);
        }

        $output->writeln(sprintf(
            '   - [TOTAL] generated class ACEs%s for %s objects (added %s, updated %s)',
            $objectOwnersMsg,
            $count,
            $countAdded,
            $countUpdated
        ));
    }
}
