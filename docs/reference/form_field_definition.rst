Form field definition
=====================

Example
-------

.. code-block:: php

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Form\Type\ModelType;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Sonata\AdminBundle\Validator\ErrorElement;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('author', ModelType::class, [], ['edit' => 'list'])
                ->add('enabled')
                // you can define help messages using Symfony help option
                ->add('title', null, ['help' => 'help_post_title'])
                ->add('abstract', null, ['required' => false])
                ->add('content');
        }

        public function validate(ErrorElement $errorElement, $object)
        {
            // conditional validation, see the related section for more information
            if ($object->getEnabled()) {
                // abstract cannot be empty when the post is enabled
                $errorElement
                    ->with('abstract')
                        ->assertNotBlank()
                        ->assertNotNull()
                    ->end()
                ;
            }
        }
    }

.. note::

    By default, the form framework always sets ``required=true`` for each
    field. This can be an issue for HTML5 browsers as they provide client-side
    validation.

Available Types
---------------

    - array
    - checkbox
    - choice
    - datetime
    - decimal
    - integer
    - text
    - date
    - time
    - datetime

If no type is set, the Admin class will use the one set in the doctrine mapping
definition.

Short Object Placeholder
------------------------

When using Many-to-One or One-to-One relations with Sonata Type fields, a short
object description is used to represent the target object. If no object is selected,
a "No selection" placeholder will be used. If you want to customize this placeholder,
you can use the corresponding option in the form field definition::

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Form\Type\ModelListType;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('enabled', null, ['required' => false])
                    ->add('author', ModelListType::class, [], [
                        'placeholder' => 'No author selected',
                    ])
                ->end();
        }
    }

This placeholder is translated using the SonataAdminBundle catalogue.

Advanced Usage: File Management
-------------------------------

If you want to use custom types from the Form framework you must use the
``addType`` method. (The ``add`` method uses the information provided by the
model definition)::

    namespace Sonata\MediaBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;

    final class MediaAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('name', null, ['required' => false])
                ->add('enabled', null, ['required' => false])
                ->add('authorName', null, ['required' => false])
                ->add('cdnIsFlushable', null, ['required' => false])
                ->add('description', null, ['required' => false])
                ->add('copyright', null, ['required' => false])
                ->add('binaryContent', 'file', ['required' => false]);
        }
  }

.. note::

    By setting ``type=false`` in the file definition, the Form framework will
    provide an instance of ``UploadedFile`` for the ``Media::setBinaryContent``
    method. Otherwise, the full path will be provided.

Advanced Usage: Many-to-One
---------------------------

If you have many ``Post``s linked to one ``User``, then the ``Post`` form should
display a ``User`` field.

SonataAdminBundle provides 3 edit options:

 - ``standard``: default value, the ``User`` list is set in a select widget
 - ``list``: the ``User`` list is set in a model where you can search and select a user
 - ``inline``: embed the ``User`` form into the ``Post`` form, great for one-to-one, or if your want to allow the user to edit the ``User`` information.

With the ``standard`` and ``list`` options, you can create a new ``User`` by clicking on the "+" icon::

    namespace Sonata\NewsBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Form\Type\ModelListType;
    use Sonata\AdminBundle\Form\Type\ModelType;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\AdminBundle\Show\ShowMapper;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

    final class PostAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->with('General')
                    ->add('enabled', null, ['required' => false])
                    ->add('author', ModelListType::class, [

                        // Specify a custom label
                        'btn_add' => 'Add author',

                        // which will be translated
                        'btn_list' => 'button.list',

                        // or hide the button
                        'btn_delete' => false,

                        // Custom translation domain for buttons
                        'btn_catalogue' => 'SonataNewsBundle',
                    ], ['edit' => 'list'])
                    ->add('title')
                    ->add('abstract')
                    ->add('content')
                ->end()
                ->with('Tags')
                    ->add('tags', ModelType::class, ['expanded' => true])
                ->end()
                ->with('Options', ['collapsed' => true])
                    ->add('commentsCloseAt')
                    ->add('commentsEnabled', null, ['required' => false])
                    ->add('commentsDefaultStatus', ChoiceType::class, [
                        'choices' => Comment::getStatusList(),
                    ])
                ->end()
            ;
        }
    }

Advanced Usage: One-to-Many
---------------------------

Let's say you have a ``Gallery`` that links to some ``Media``s with a join table
``galleryHasMedias`` you can reference them like::

    namespace Sonata\MediaBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\Form\Type\CollectionType;

    final class GalleryAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('code')
                ->add('enabled')
                ->add('name')
                ->add('defaultFormat')
                ->add('galleryHasMedias', CollectionType::class);
        }
    }

Add a new ``Media`` (via ``galleryHasMedias``) row by defining one of these options:

``edit``
    ``inline`` or ``standard``, the inline mode allows you to add new rows

``inline``
    ``table`` or ``standard``, the fields are displayed into table

``sortable``
    if the model has a position field, you can enable a drag and drop sortable effect by setting ``sortable=field_name``

After choosing your action, your admin would llok like this::

    namespace Sonata\MediaBundle\Admin;

    use Sonata\AdminBundle\Admin\AbstractAdmin;
    use Sonata\AdminBundle\Form\FormMapper;
    use Sonata\AdminBundle\Datagrid\DatagridMapper;
    use Sonata\AdminBundle\Datagrid\ListMapper;
    use Sonata\Form\Type\CollectionType;

    final class GalleryAdmin extends AbstractAdmin
    {
        protected function configureFormFields(FormMapper $formMapper)
        {
            $formMapper
                ->add('code')
                ->add('enabled')
                ->add('name')
                ->add('defaultFormat')
                ->add('galleryHasMedias', CollectionType::class, [], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable'  => 'position',
                ]);
        }
    }
