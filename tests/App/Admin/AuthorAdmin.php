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

namespace Sonata\DoctrineMongoDBAdminBundle\Tests\App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\Form\Type\CollectionType;

final class AuthorAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->addIdentifier('name');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('address.street')
            ->add('phoneNumbers.number');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id', null, [
                'attr' => [
                    'class' => 'author_id',
                ],
            ])
            ->add('name', null, [
                'attr' => [
                    'class' => 'author_name',
                ],
            ])
            ->add('address', AdminType::class, [
                'attr' => [
                    'class' => 'author_address',
                ],
            ])
            ->add('phoneNumbers', CollectionType::class, [
                'attr' => [
                    'class' => 'author_phoneNumbers',
                ],
            ], [
                'edit' => 'inline',
                'inline' => 'table',
            ]);
    }
}
