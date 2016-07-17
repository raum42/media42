<?php
/**
 * media42 (www.raum42.at)
 *
 * @link http://www.raum42.at
 * @copyright Copyright (c) 2010-2016 raum42 OG (http://www.raum42.at)
 *
 */

namespace Media42\Command;

use Media42\MediaEvent;
use Media42\MediaOptions;
use Media42\Model\Media;
use Core42\Command\AbstractCommand;
use Media42\TableGateway\MediaTableGateway;

class DeleteCommand extends AbstractCommand
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
            $this->addError("media", "media not found");
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

        $removedCount = 0;
        $dir = scandir($baseDir);
        foreach ($dir as $_entry) {
            if ($_entry == ".." || $_entry == ".") {
                $removedCount++;
                continue;
            }

            if (strpos($_entry, $filename) === 0) {
                @unlink($baseDir . $_entry);
                $removedCount++;
            }
        }

        if (count($dir) == ($removedCount)) {
            @rmdir($baseDir);
        }

        $this->getTableGateway(MediaTableGateway::class)->delete($this->media);

        $this
            ->getServiceManager()
            ->get('Media42\EventManager')
            ->trigger(MediaEvent::EVENT_DELETE, $this->media);

        $this->getServiceManager()->get('Cache\Media')->removeItem('media_'. $this->media->getId());
    }
}