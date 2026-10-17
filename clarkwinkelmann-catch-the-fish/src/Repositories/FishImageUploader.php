<?php

namespace ClarkWinkelmann\CatchTheFish\Repositories;

use ClarkWinkelmann\CatchTheFish\Fish;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\Interfaces\ImageInterface;

class FishImageUploader
{
    protected Filesystem $assets;

    public function __construct(Factory $filesystemFactory)
    {
        $this->assets = $filesystemFactory->disk('catch-the-fish');
    }

    public function upload(Fish $fish, ImageInterface $image)
    {
        $encodedImage = $image->scaleDown(300, 200)->toPng();

        $imagePath = Str::random() . '.png';

        $this->remove($fish);
        $fish->image = $imagePath;

        $this->assets->put($imagePath, $encodedImage->toString());
    }

    public function remove(Fish $fish)
    {
        $imagePath = $fish->image;

        if ($imagePath && $this->assets->exists($imagePath)) {
            $this->assets->delete($imagePath);
        }

        $fish->image = null;
    }
}
