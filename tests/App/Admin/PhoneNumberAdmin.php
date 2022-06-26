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
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineMongoDBAdminBundle\Tests\App\Document\PhoneNumber;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<PhoneNumber>
 */
final class PhoneNumberAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('number');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('number', TextType::class, [
                'attr' => [
                    'class' => 'phone_number_number',
                ],
                'empty_data' => '',
            ]);
    }
}
