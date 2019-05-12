<?php

namespace App\Converter;

use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory as DoctrineMetadataFactory;

/**
 * Class ModelConverter.
 */
class ModelConverter
{
    const FORMAT = 'json';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ObjectNormalizer
     */
    protected $objectNormalizer;

    /**
     * @var GetSetMethodNormalizer
     */
    protected $getSetNormalizer;

    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * @var DoctrineMetadataFactory
     */
    protected $ormMetadataFactory;

    const DATE_FORMATS = [
        Type::DATETIME,
    ];

    /**
     * ModelConverter constructor.
     *
     * @param EntityManagerInterface $emi
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function __construct(EntityManagerInterface $emi)
    {
        $this->ormMetadataFactory = new DoctrineMetadataFactory();
        $this->ormMetadataFactory->setEntityManager($emi);
        $this->classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $this->objectNormalizer = new ObjectNormalizer($this->classMetadataFactory);
        $this->getSetNormalizer = new GetSetMethodNormalizer($this->classMetadataFactory);
        /* @var SerializerInterface $serializer */
        $this->serializer = new Serializer([$this->objectNormalizer, $this->getSetNormalizer], [new JsonEncoder()]);
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @param string $json
     * @param string $modelType
     * @param array  $ignoredField
     *
     * @return object
     */
    public function convertJsonToModel(string $json, string $modelType, array $ignoredField = [])
    {
        $model = $this->serializer->deserialize($json, $modelType, self::FORMAT,
            [
                'ignored_attributes' => $ignoredField,
            ]
        );

        return $model;
    }

    /**
     * @param $data
     * @param array $groups
     * @param string $dateFormat
     * @return array|bool|float|int|string
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function convertModelToArray($data, array $groups, string $dateFormat = 'Y-m-d H:i:s')
    {
        if (0 == count($groups)) {
            throw new \InvalidArgumentException('Field groups should be defined.');
        }
        $fieldsToNorm = $this->filterDateTimeFields($data);

        $dateTimeCallBack = function ($dateTime) use ($dateFormat) {
            return $dateTime instanceof \DateTimeInterface
                ? $dateTime->format($dateFormat)
                : '';
        };

        $toNormalize = [];
        foreach ($fieldsToNorm as $field) {
            $toNormalize[$field] = $dateTimeCallBack;
        }

        $data = $this->objectNormalizer->normalize($data, null,
            [
                'groups'    => $groups,
                'callbacks' => $toNormalize,
            ]
        );

        return $data;
    }

    /**
     * @param $object
     *
     * @return array
     *
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    protected function filterDateTimeFields($object): array
    {
        $dateFields = [];
        $classMetaData = $this->ormMetadataFactory->getMetadataFor(get_class($object));
        $classFields = $classMetaData->getFieldNames();

        foreach ($classFields as $field) {
            $fieldType = $classMetaData->getTypeOfField($field);
            if (in_array($fieldType, self::DATE_FORMATS)) {
                $dateFields[] = $field;
            }
        }

        return $dateFields;
    }
}