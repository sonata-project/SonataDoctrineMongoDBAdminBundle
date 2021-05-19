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

namespace Sonata\DoctrineMongoDBAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @final since sonata-project/doctrine-mongodb-admin-bundle 3.5.
 */
class CallbackFilter extends Filter
{
    /**
     * NEXT_MAJOR: Remove $alias parameter.
     *
     * @return void
     */
    public function filter(BaseProxyQueryInterface $query, $alias, $field, $data)
    {
        /* NEXT_MAJOR: Remove this deprecation and update the typehint */
        if (!$query instanceof ProxyQueryInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/doctrine-mongodb-admin-bundle 3.8'
                .' and will throw a \TypeError error in version 4.0. You MUST pass an instance of %s instead.',
                \get_class($query),
                __METHOD__,
                ProxyQueryInterface::class
            ), \E_USER_DEPRECATED);
        }

        $callable = $this->getOption('callback');

        if (!\is_callable($callable)) {
            throw new \RuntimeException(sprintf(
                'Please provide a valid callback option "filter" for field "%s"',
                $this->getName()
            ));
        }

        // NEXT_MAJOR: Remove next line
        $callbackReflection = $this->reflectCallable($callable);

        // NEXT_MAJOR: Remove the entire if block
        if (null !== $callbackReflection && isset($callbackReflection->getParameters()[3])) {
            if ($callbackReflection->getParameters()[3]->hasType()
                && $callbackReflection->getParameters()[3]->getType() instanceof \ReflectionNamedType
                && FilterData::class === $callbackReflection->getParameters()[3]->getType()->getName()
            ) {
                $data = FilterData::fromArray($data);
            } else {
                @trigger_error(sprintf(
                    'Not adding "%1$s" as type declaration for argument 4 is deprecated since'
                    .' sonata-project/doctrine-mongodb-admin-bundle 3.9 and the argument will be a "%1$s" instance in version 4.0.',
                    FilterData::class
                ), \E_USER_DEPRECATED);
            }
        }

        // NEXT_MAJOR: Remove $alias parameter.
        $isActive = \call_user_func($callable, $query, $alias, $field, $data);

        if (!\is_bool($isActive)) {
            @trigger_error(
                'Using another return type than boolean for the callback option is deprecated'
                .' since sonata-project/doctrine-mongodb-admin-bundle 3.9 and will throw an exception in version 4.0.',
                \E_USER_DEPRECATED
            );

            // NEXT_MAJOR: Remove next line.
            $isActive = (bool) $isActive;

            // NEXT_MAJOR: Uncomment the following code instead of the deprecation.
//            throw new \UnexpectedValueException(sprintf(
//                'The callback should return a boolean, %s returned',
//                \is_object($isActive) ? 'instance of "'.\get_class($isActive).'"' : '"'.\gettype($isActive).'"'
//            ));
        }

        // NEXT_MAJOR: Remove next line.
        $activeCallback = $this->getOption('active_callback');

        // NEXT_MAJOR: Remove the entire following if-else.
        if (null !== $activeCallback) {
            @trigger_error(
                sprintf(
                    'Using "active_callback" option in "%s" is deprecated since'
                .' sonata-project/doctrine-mongodb-admin-bundle 3.9 and will be removed version 4.0.'
                .' You MUST return a boolean value for the "callback" option instead.',
                    self::class
                ),
                \E_USER_DEPRECATED
            );

            if (\is_callable($activeCallback)) {
                $this->active = \call_user_func($activeCallback, $data);

                return;
            }
        }

        // NEXT_MAJOR: Remove next line.
        $hasValue = $data instanceof FilterData
            ? $data->hasValue() && $data->getValue()
            : isset($data['value']) && $data['value'];

        // NEXT_MAJOR: Remove next line and uncomment the following one.
        $this->active = $isActive && $hasValue;
        // $this->active = $isActive;
    }

    public function getDefaultOptions()
    {
        return [
            'callback' => null,
            // NEXT_MAJOR: Remove next line.
            'active_callback' => null,
            'field_type' => TextType::class,
            'operator_type' => HiddenType::class,
            'operator_options' => [],
        ];
    }

    public function getRenderSettings()
    {
        return [DefaultType::class, [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
                'label' => $this->getLabel(),
        ]];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * extracted from https://github.com/getsentry/sentry-php/blob/4f6f8fa701e5db53c04f471b139e7d4f85831f17/src/Serializer/AbstractSerializer.php#L272-L283
     */
    private function reflectCallable(callable $callable): ?\ReflectionFunctionAbstract
    {
        if (\is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        } elseif ($callable instanceof \Closure || \is_string($callable)) {
            return new \ReflectionFunction($callable);
        } elseif (method_exists($callable, '__invoke')) {
            return new \ReflectionMethod($callable, '__invoke');
        }

        return null;
    }
}
