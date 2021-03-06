<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\MediaBundle\Twig\Extension;

use Sylius\Component\Media\Model\ImageInterface;
use Symfony\Cmf\Bundle\MediaBundle\Templating\Helper\CmfMediaHelper;

/**
 * @author Aram Alipoor <aram.alipoor@gmail.com>
 */
class SyliusImageExtension extends \Twig_Extension
{
    /**
     * @var CmfMediaHelper
     */
    protected $mediaHelper;

    /**
     * @param CmfMediaHelper $mediaHelper
     */
    public function __construct(
        CmfMediaHelper $mediaHelper
    ) {
        $this->mediaHelper = $mediaHelper;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('sylius_image_url',
                array($this, 'getImageUrl'),
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * @param ImageInterface|null $image
     * @param array|string $options Imagine filter name or an array options to to be passed to media helper.
     * @param string $default
     *
     * @return string Image display url or a default url if image is not available.
     */
    public function getImageUrl($image, $options = array(), $default = '') {
        if (is_string($options)) {
            $options = array(
                'imagine_filter' => $options
            );
        }

        if ($image instanceof ImageInterface) {
            $media = $image->getMedia();
        } else {
            $media = $image;
        }

        if ($media) {
            return $this->mediaHelper->displayUrl($media, $options);
        } else {
            return $default;
        }
    }

    public function getName() {
        return 'sylius_image';
    }
}
