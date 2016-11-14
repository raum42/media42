<?php

/*
 * media42
 *
 * @package media42
 * @link https://github.com/raum42/media42
 * @copyright Copyright (c) 2010 - 2016 raum42 (https://www.raum42.at)
 * @license MIT License
 * @author raum42 <kiwi@raum42.at>
 */

namespace Media42;

use Media42\Model\Media;
use Media42\Selector\MediaSelector;
use Media42\TableGateway\MediaTableGateway;
use Psr\Cache\CacheItemPoolInterface;

class MediaUrl
{
    /**
     * @var MediaTableGateway;
     */
    protected $mediaTableGateway;

    /**
     * @var MediaOptions
     */
    protected $mediaOptions;

    /**
     * @var MediaSelector
     */
    protected $mediaSelector;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @param MediaTableGateway $mediaTableGateway
     * @param MediaOptions $mediaOptions
     * @param MediaSelector $mediaSelector
     * @param string $basePath
     */
    public function __construct(
        MediaTableGateway $mediaTableGateway,
        MediaOptions $mediaOptions,
        MediaSelector $mediaSelector,
        $basePath
    ) {
        $this->mediaTableGateway = $mediaTableGateway;
        $this->mediaOptions = $mediaOptions;
        $this->mediaSelector = $mediaSelector;
        $this->basePath = $basePath;
    }

    /**
     * @param $mediaId
     * @param null $dimension
     * @return string
     */
    public function getUrl($mediaId, $dimension = null)
    {
        $media = $this->mediaSelector->setMediaId($mediaId)->getResult();
        if (empty($media)) {
            return '';
        }

        $baseUrl = "";
        if ($this->mediaOptions->getPrependBasePath() === true) {
            $baseUrl = $this->basePath;
        }

        if (strlen($this->mediaOptions->getUrl())) {
            $baseUrl .= $this->mediaOptions->getUrl();
        }

        if (substr($media->getMimeType(), 0, 6) != 'image/' || $dimension === null) {
            return $baseUrl . $media->getDirectory() . $media->getFilename();
        }

        $dimension = $this->mediaOptions->getDimension($dimension);
        if ($dimension === null) {
            return '';
        }

        $pos = strrpos($media->getFilename(), '.');
        $filename = substr($media->getFilename(), 0, $pos);
        $extension = substr($media->getFilename(), $pos);

        $filename .= '-'
            . (($dimension['width'] == 'auto') ? '000' : $dimension['width'])
            . 'x'
            . (($dimension['height'] == 'auto') ? '000' : $dimension['height'])
            . $extension;


        return $baseUrl . $media->getDirectory() . rawurlencode($filename);
    }
}
