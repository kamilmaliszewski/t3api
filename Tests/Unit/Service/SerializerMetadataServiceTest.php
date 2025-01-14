<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use ReflectionClass;
use ReflectionException;
use SourceBroker\T3api\Annotation\Serializer\Groups;
use SourceBroker\T3api\Annotation\Serializer\Type\Image;
use SourceBroker\T3api\Annotation\Serializer\Type\RecordUri;
use SourceBroker\T3api\Service\SerializerMetadataService;

/**
 * Class SerializerMetadataServiceTest
 */
class SerializerMetadataServiceTest extends UnitTestCase
{
    /**
     * @return array
     */
    public function getPropertyMetadataFromAnnotationsReturnsCorrectValueDataProvider(): array
    {
        return [
            'Groups' => [
                function () {
                    $groups = new Groups();
                    $groups->groups = ['api_example_group_1234', 'api_another_example_group'];

                    return [$groups];
                },
                [
                    'groups' => [
                        'api_example_group_1234',
                        'api_another_example_group',
                    ],
                ],
            ],
            'Type - Image' => [
                function () {
                    $image = new Image();
                    $image->width = '800c';
                    $image->height = '600';

                    return [$image];
                },
                [
                    'type' => 'Image<"800c","600">',
                ],
            ],
            'Type - RecordUri' => [
                function () {
                    $recordUri = new RecordUri();
                    $recordUri->identifier = 'tx_example_identifier';

                    return [$recordUri];
                },
                [
                    'type' => 'RecordUri<"tx_example_identifier">',
                ],
            ],
            'RecordUri with groups' => [
                function () {
                    $recordUri = new RecordUri();
                    $recordUri->identifier = 'tx_another_identifier';
                    $groups = new Groups();
                    $groups->groups = ['api_group_sample', 'api_group_sample_2'];

                    return [$groups, $recordUri];
                },
                [
                    'type' => 'RecordUri<"tx_another_identifier">',
                    'groups' => [
                        'api_group_sample',
                        'api_group_sample_2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param callable $annotations
     * @param array $expectedResult
     *
     * @dataProvider getPropertyMetadataFromAnnotationsReturnsCorrectValueDataProvider
     * @test
     *
     * @throws ReflectionException
     */
    public function getPropertyMetadataFromAnnotationsReturnsCorrectValue(callable $annotations, array $expectedResult)
    {
        self::assertEqualsCanonicalizing(
            $expectedResult,
            self::callProtectedMethod('getPropertyMetadataFromAnnotations', [$annotations()])
        );
    }

    /**
     * @return array
     */
    public function parsePropertyTypeReturnsCorrectValueDataProvider(): array
    {
        return [
            '\DateTime' => [
                '\DateTime',
                sprintf('DateTime<"%s">', \DateTimeInterface::RFC3339_EXTENDED),
            ],
            '\DateTime|null' => [
                '\DateTime|null',
                sprintf('DateTime<"%s">', \DateTimeInterface::RFC3339_EXTENDED),
            ],
            'null|\DateTime' => [
                'null|\DateTime',
                sprintf('DateTime<"%s">', \DateTimeInterface::RFC3339_EXTENDED),
            ],
            'DateTime|null' => [
                '\DateTime|null',
                sprintf('DateTime<"%s">', \DateTimeInterface::RFC3339_EXTENDED),
            ],
            'DateTime | null' => [
                'DateTime | null',
                sprintf('DateTime<"%s">', \DateTimeInterface::RFC3339_EXTENDED),
            ],
            '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                '\TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'TYPO3\CMS\Extbase\Persistence\ObjectStorage<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            '\TYPO3\CMS\Extbase\Domain\Model\FileReference' => [
                '\TYPO3\CMS\Extbase\Domain\Model\FileReference',
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
            ],
            'TYPO3\CMS\Extbase\Domain\Model\FileReference' => [
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
                'TYPO3\CMS\Extbase\Domain\Model\FileReference',
            ],
            'string' => [
                'string',
                'string',
            ],
            'string And here, in same lane as var annotation, is description for the property' => [
                'string And here, in same lane as var annotation, is description for the property',
                'string',
            ],
            'boolean' => [
                'boolean',
                'boolean',
            ],
            'bool' => [
                'bool',
                'bool',
            ],
            'int' => [
                'int',
                'int',
            ],
            'integer' => [
                'integer',
                'integer',
            ],
            'double' => [
                'double',
                'double',
            ],
            'float' => [
                'float',
                'float',
            ],
            'array<string>' => [
                'array<string>',
                'array<string>',
            ],
            'array<string> And some additional description here' => [
                'array<string> And some additional description here',
                'array<string>',
            ],
            'array<\TYPO3\CMS\Extbase\Domain\Model\FileReference>' => [
                'array<\TYPO3\CMS\Extbase\Domain\Model\FileReference>',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'string[]' => [
                'string[]',
                'array<string>',
            ],
            '[]' => [
                '[]',
                'array',
            ],
            'array' => [
                'array',
                'array',
            ],
            '\TYPO3\CMS\Extbase\Domain\Model\FileReference[]' => [
                '\TYPO3\CMS\Extbase\Domain\Model\FileReference[]',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            'TYPO3\CMS\Extbase\Domain\Model\FileReference[]' => [
                'TYPO3\CMS\Extbase\Domain\Model\FileReference[]',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
            '\TYPO3\CMS\Extbase\Domain\Model\FileReference[] Additional description goes here' => [
                '\TYPO3\CMS\Extbase\Domain\Model\FileReference[]',
                'array<TYPO3\CMS\Extbase\Domain\Model\FileReference>',
            ],
        ];
    }

    /**
     * @param string $varAnnotation
     * @param string $expectedType
     *
     * @dataProvider parsePropertyTypeReturnsCorrectValueDataProvider
     * @test
     *
     * @throws ReflectionException
     */
    public function parsePropertyTypeReturnsCorrectValue(string $varAnnotation, string $expectedType)
    {
        self::assertEquals(
            $expectedType,
            self::callProtectedMethod('parsePropertyType', [$varAnnotation])
        );
    }

    /**
     * @param $methodName
     * @param array $arguments
     * @param object|null $object
     *
     * @throws ReflectionException
     * @return mixed
     */
    protected static function callProtectedMethod($methodName, array $arguments = [], object $object = null)
    {
        $serializerMetadataServiceReflection = new ReflectionClass(SerializerMetadataService::class);
        $method = $serializerMetadataServiceReflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object ? $object : null, $arguments);
    }
}
