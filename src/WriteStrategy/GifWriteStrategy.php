<?php

namespace Sokil\Image\WriteStrategy;

use Sokil\Image\AbstractWriteStrategy;

class GifWriteStrategy extends AbstractWriteStrategy
{   
    public function write($resource)
    {
        if(!is_resource($resource)  || 'gd' !== get_resource_type($resource)) {
            throw new \Exception('Resource must be given');
        }

        if (!empty($this->targetPath)) {
            $targetPath = $this->targetPath;
            if('gif' !== strtolower(pathinfo($targetPath, PATHINFO_EXTENSION))) {
                $targetPath .= '.gif';
            }
        } else {
            $targetPath = null;
        }

        if (!imagegif($resource, $targetPath)) {
            throw new \Exception('Error writing GIF file');
        }
    }
}