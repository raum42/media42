<?php

/*
 * media42
 *
 * @package media42
 * @link https://github.com/kiwi-suite/media42
 * @copyright Copyright (c) 2010 - 2017 kiwi suite (https://kiwi-suite.com)
 * @license MIT License
 * @author kiwi suite <dev@kiwi-suite.com>
 */

namespace Media42\Command;

use Media42\MediaOptions;
use Media42\Model\Media;
use Core42\Command\AbstractCommand;
use Media42\TableGateway\MediaTableGateway;

class CleanupDataDirectory extends AbstractCommand
{
    /**
     * @var Media
     */
    protected $media;

    /**
     * @var int
     */
    protected $mediaId;

    /**
     * @var bool
     */
    protected $transaction = false;

    /**
     * @param int $mediaId
     * @return $this
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;

        return $this;
    }

    /**
     * @param Media $media
     * @return $this
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     *
     */
    protected function preExecute()
    {
        if ($this->mediaId > 0) {
            $this->media = $this->getTableGateway(MediaTableGateway::class)->selectByPrimary((int) $this->mediaId);
        }

        if (empty($this->media)) {
            $this->addError('media', 'media not found');
        }
    }

    /**
     * @return mixed|void
     */
    protected function execute()
    {
        /* @var MediaOptions $mediaOptions */
        $mediaOptions = $this->getServiceManager()->get(MediaOptions::class);

        $baseDir = $mediaOptions->getPath() . $this->media->getDirectory();

        $filename = $this->media->getFilename();
        $filename = substr($filename, 0, strrpos($filename, '.'));

        $dir = scandir($baseDir);
        foreach ($dir as $_entry) {
            if ($_entry == '..' || $_entry == '.') {
                continue;
            }

            if (strpos($_entry, $filename) === 0) {
                @unlink($baseDir . $_entry);
            }
        }

        while (rtrim($baseDir, DIRECTORY_SEPARATOR) != rtrim($mediaOptions->getPath(), DIRECTORY_SEPARATOR)) {
            $dir = scandir($baseDir);
            $empty = true;
            foreach ($dir as $_entry) {
                if ($_entry == '..' || $_entry == '.') {
                    continue;
                }
                $empty = false;
                break;
            }

            if ($empty === false) {
                break;
            }
            @rmdir($baseDir);
            $baseDir = dirname($baseDir);
        }
    }
}
