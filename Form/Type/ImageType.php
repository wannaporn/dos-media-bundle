<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\MediaBundle\Form\Type;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ORM\EntityManager;
use Sylius\Bundle\MediaBundle\Form\DataTransformer\PathToDocumentTransformer;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Component\Media\Model\Image;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Sylius image form type.
 *
 * @author Aram Alipoor <aram.alipoor@gmail.com>
 */
class ImageType extends AbstractResourceType
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param string $dataClass
     * @param array $validationGroups
     * @param DocumentManager $documentManager
     * @param EntityManager $entityManager
     */
    public function __construct(
        $dataClass,
        array $validationGroups,
        DocumentManager $documentManager,
        EntityManager $entityManager
    ) {
        parent::__construct($dataClass, $validationGroups);

        $this->documentManager = $documentManager;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder
                ->create('media', 'cmf_media_image')
                ->addViewTransformer(new PathToDocumentTransformer($this->documentManager))
        );

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Image $data */
            $data = $event->getData();

            if (null !== ($media = $data->getMedia())) {
                if ($media->getId() !== $data->getMediaId()) {
                    // This actually helps trigger preUpdate doctrine event since
                    // doctrine is not tracking changes on $media field of Image entity.
                    //
                    // Here we forcefully update $mediaId (which is tracked by doctrine) to trigger
                    // a change if a new media has been uploaded/selected.
                    $data->setMediaId($media->getId());
                }
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sylius_image';
    }
}
