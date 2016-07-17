<?php
/**
 * media42 (www.raum42.at)
 *
 * @link http://www.raum42.at
 * @copyright Copyright (c) 2010-2016 raum42 OG (http://www.raum42.at)
 *
 */

namespace Media42;

use Media42\Model\Media;
use Media42\TableGateway\MediaTableGateway;
use Zend\Cache\Storage\StorageInterface;

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
     * @var StorageInterface
     */
    protected $cache;

    /**
     * @param MediaTableGateway $mediaTableGateway
     * @param MediaOptions $mediaOptions
     * @param StorageInterface $cache
     */
    public function __construct(
        MediaTableGateway $mediaTableGateway,
        MediaOptions $mediaOptions,
        StorageInterface $cache
    ) {
        $this->mediaTableGateway = $mediaTableGateway;
        $this->mediaOptions = $mediaOptions;
        $this->cache = $cache;
    }

    /**
     * @param $mediaId
     * @param null $dimension
     * @return string
     */
    public function getUrl($mediaId, $dimension = null)
    {
        $media = $this->loadMedia($mediaId);
        if (empty($media)) {
            return "";
        }

        if (substr($media->getMimeType(), 0, 6) != "image/" || $dimension === null) {
            return $this->mediaOptions->getUrl() . $media->getDirectory() . $media->getFilename();
        }

        $dimension = $this->mediaOptions->getDimension($dimension);
        if ($dimension === null) {
            return "";
        }

        $pos = strrpos($media->getFilename(), '.');
        $filename = substr($media->getFilename(), 0, $pos);
        $extension = substr($media->getFilename(), $pos);

        $filename .= '-'
            . (($dimension['width'] == 'auto') ? '000' : $dimension['width'])
            . 'x'
            . (($dimension['height'] == 'auto') ? '000' : $dimension['height'])
            . $extension;


        return $this->mediaOptions->getUrl() . $media->getDirectory() . rawurlencode($filename);
    }

    /**
     * @param $mediaId
     * @return Media|null
     * @throws \Exception
     */
    public function loadMedia($mediaId)
    {
        if (empty($mediaId)) {
            return null;
        }
        if (!$this->cache->hasItem('media_'. $mediaId)) {
            $this->cache->setItem(
                'media_'. $mediaId,
                $this->mediaTableGateway->selectByPrimary((int) $mediaId)
            );
        }

        return $this->cache->getItem('media_'. $mediaId);
    }
}