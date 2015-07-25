<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\MediaBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Sylius\Component\Media\Model\ImageInterface;
use Symfony\Cmf\Bundle\MediaBundle\ImageInterface as CmfImageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Aram Alipoor <aram.alipoor@gmail.com>
 */
class ImageMediaReferenceListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'postLoad',
        );
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $object = $event->getEntity();

        if ($object instanceof ImageInterface) {
            if (null !== ($media = $object->getMedia())) {
                if (null === ($id = $media->getId())) {

                    /** @var CmfImageInterface $media */
                    $dm = $this->getDocumentManager();
                    $dm->persist($media);
                    $dm->flush();

                    $id = $media->getId();
                }

                $object->setMediaId($id);
            }
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(LifecycleEventArgs $event) {
        $this->prePersist($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $object = $event->getObject();

        if ($object instanceof ImageInterface) {
            if (null !== $object->getMediaId()) {

                /** @var CmfImageInterface $media */
                $media = $this
                    ->getDocumentManager()
                    ->find(null, $object->getMediaId());

                $object->setMedia($media);
            }
        }
    }

    /**
     * @return ObjectManager
     */
    protected function getDocumentManager() {
        return $this->container->get('doctrine_phpcr.odm.document_manager');
    }
}
