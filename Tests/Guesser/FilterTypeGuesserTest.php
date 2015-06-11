<?php

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Component\Form\Guess\TypeGuess;

class FilterTypeGuesserOverride extends FilterTypeGuesser
{
    private $metadata;

    public function setMetadataTest($metadata)
    {
        $this->metadata = $metadata;
    }

    public function getParentMetadataForProperty($baseClass, $propertyFullName, ModelManager $modelManager)
    {
        return array($this->metadata, 'propertyName', array('parent_association_mapping'));
    }
}

class FilterTypeGuesserTest extends PHPUnit_Framework_TestCase
{
    /** @var ClassMetadata */
    private $metadata;
    /** @var ModelManager */
    private $manager;

    public function setUp()
    {
        // The Metadata
        $this->metadata = $this->getMockBuilder('\Doctrine\ODM\MongoDB\Mapping\ClassMetadata', array('hasAssociation'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata->method('hasAssociation')->willReturn('true');
        $this->metadata->fieldMappings = array(
            'propertyName' => array(
                'type'      => null,
                'fieldName' => 'property_name',
            ),
        );

        // To create the Manager
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = new ModelManager($registry);
    }

    /**
     * @test
     */
    public function canGuessTypeOfDate()
    {
        $this->metadata->method('getTypeOfField')->willReturn('date');

        $filterTypeGuesser = new FilterTypeGuesserOverride();
        $filterTypeGuesser->setMetadataTest($this->metadata);

        /** @var TypeGuess $guessType */
        $guessType = $filterTypeGuesser->guessType('', '', $this->manager);
        $this->assertEquals('doctrine_mongo_date', $guessType->getType());
    }

    /**
     * @test
     */
    public function canGuessTypeOfDateTime()
    {
        // this doesnt exist but whatever..
        $this->metadata->method('getTypeOfField')->willReturn('datetime');

        $filterTypeGuesser = new FilterTypeGuesserOverride();
        $filterTypeGuesser->setMetadataTest($this->metadata);

        /** @var TypeGuess $guessType */
        $guessType = $filterTypeGuesser->guessType('', '', $this->manager);
        $this->assertEquals('doctrine_mongo_datetime', $guessType->getType());
    }

    /**
     * @test
     */
    public function canGuessTypeOfTimestamp()
    {
        $this->metadata->method('getTypeOfField')->willReturn('timestamp');

        $filterTypeGuesser = new FilterTypeGuesserOverride();
        $filterTypeGuesser->setMetadataTest($this->metadata);

        /** @var TypeGuess $guessType */
        $guessType = $filterTypeGuesser->guessType('', '', $this->manager);
        $this->assertEquals('doctrine_mongo_datetime', $guessType->getType());
    }
}
