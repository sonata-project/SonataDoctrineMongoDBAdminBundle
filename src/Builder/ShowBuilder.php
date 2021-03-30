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

namespace Sonata\DoctrineMongoDBAdminBundle\Builder;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface as DeprecatedTypeGuesserInterface;

final class ShowBuilder implements ShowBuilderInterface
{
    /**
     * NEXT_MAJOR: Remove DeprecatedTypeGuesserInterface type.
     *
     * @var DeprecatedTypeGuesserInterface|TypeGuesserInterface
     */
    private $guesser;

    /**
     * @var string[]
     */
    private $templates;

    /**
     * NEXT_MAJOR: Remove DeprecatedTypeGuesserInterface type and add TypeGuesserInterface to the constructor.
     *
     * @param DeprecatedTypeGuesserInterface|TypeGuesserInterface $guesser
     * @param string[]                                            $templates
     */
    public function __construct($guesser, array $templates)
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        return new FieldDescriptionCollection();
    }

    public function addField(
        FieldDescriptionCollection $list,
        ?string $type,
        FieldDescriptionInterface $fieldDescription
    ): void {
        if (null === $type) {
            // NEXT_MAJOR: Remove the condition and keep the if part.
            if ($this->guesser instanceof TypeGuesserInterface) {
                $guessType = $this->guesser->guess($fieldDescription);
            } else {
                $guessType = $this->guesser->guessType(
                    $fieldDescription->getAdmin()->getClass(),
                    $fieldDescription->getName(),
                    $fieldDescription->getAdmin()->getModelManager()
                );
            }
            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);
        $fieldDescription->getAdmin()->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if (!$fieldDescription->getType()) {
            throw new \RuntimeException(sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), \get_class($fieldDescription->getAdmin())));
        }

        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {
            if ('id' === $fieldDescription->getType()) {
                $fieldDescription->setType('string');
            }

            if ('int' === $fieldDescription->getType()) {
                $fieldDescription->setType('integer');
            }

            $template = $this->getTemplate($fieldDescription->getType());

            if (null === $template) {
                if (ClassMetadata::ONE === $fieldDescription->getMappingType()) {
                    $template = '@SonataAdmin/CRUD/Association/show_many_to_one.html.twig';
                } elseif (ClassMetadata::MANY === $fieldDescription->getMappingType()) {
                    $template = '@SonataAdmin/CRUD/Association/show_many_to_many.html.twig';
                }
            }

            $fieldDescription->setTemplate($template);
        }

        if (\in_array($fieldDescription->getMappingType(), [ClassMetadata::ONE, ClassMetadata::MANY], true)) {
            $fieldDescription->getAdmin()->attachAdminClass($fieldDescription);
        }
    }

    /**
     * @param int|string $type
     */
    private function getTemplate($type): ?string
    {
        if (!isset($this->templates[$type])) {
            return null;
        }

        return $this->templates[$type];
    }
}
