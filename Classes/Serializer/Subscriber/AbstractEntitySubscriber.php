<?php
declare(strict_types=1);
namespace SourceBroker\T3api\Serializer\Subscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\EventDispatcher\PreDeserializeEvent;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use SourceBroker\T3api\Domain\Repository\ApiResourceRepository;
use SourceBroker\T3api\Serializer\Handler\AbstractDomainObjectHandler;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AbstractEntitySubscriber
 */
class AbstractEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @var ApiResourceRepository
     */
    protected $apiResourceRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ApiResourceRepository $apiResourceRepository
     */
    public function injectApiResourceRepository(ApiResourceRepository $apiResourceRepository): void
    {
        $this->apiResourceRepository = $apiResourceRepository;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
            ],
            [
                'event' => Events::PRE_DESERIALIZE,
                'method' => 'onPreDeserialize',
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onPostSerialize(ObjectEvent $event)
    {
        if (!$event->getObject() instanceof AbstractDomainObject) {
            return;
        }

        /** @var AbstractDomainObject $entity */
        $entity = $event->getObject();

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $this->addForceEntityProperties($entity, $visitor);
        $this->addIri($entity, $visitor);
    }

    /**
     * @param PreDeserializeEvent $event
     */
    public function onPreDeserialize(PreDeserializeEvent $event)
    {
        // Changes type to the custom one to make it possible to handle data with serializer handler
        if (
            is_subclass_of($event->getType()['name'], AbstractDomainObject::class)
            && $event->getContext()->getDepth() > 1
        ) {
            $event->setType(
                AbstractDomainObjectHandler::TYPE,
                [
                    'targetType' => $event->getType()['name'],
                ]
            );
        }
    }

    /**
     * @param AbstractDomainObject $entity
     * @param JsonSerializationVisitor $visitor
     *
     * @return void
     */
    protected function addForceEntityProperties(AbstractDomainObject $entity, JsonSerializationVisitor $visitor): void
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3api']['forceEntityProperties'] as $property) {
            $value = ObjectAccess::getProperty($entity, $property);
            $visitor->visitProperty(
                new StaticPropertyMetadata(AbstractDomainObject::class, $property, $value),
                $value
            );
        }
    }

    /**
     * @param AbstractDomainObject $entity
     * @param JsonSerializationVisitor $visitor
     *
     * @return void
     */
    protected function addIri(AbstractDomainObject $entity, JsonSerializationVisitor $visitor): void
    {
        $apiResource = $this->apiResourceRepository->getByEntity($entity);
        if ($apiResource && $apiResource->getMainItemOperation()) {
            // @todo should be generated with symfony router
            $iri = str_replace('{id}', $entity->getUid(), $apiResource->getMainItemOperation()->getRoute()->getPath());
            $visitor->visitProperty(
                new StaticPropertyMetadata(AbstractDomainObject::class, '@id', $iri),
                $iri
            );
        }
    }
}
