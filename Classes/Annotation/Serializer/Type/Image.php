<?php

declare(strict_types=1);
namespace SourceBroker\T3api\Annotation\Serializer\Type;

use SourceBroker\T3api\Serializer\Handler\ImageHandler;

/**
 * @Annotation
 * @Target({"PROPERTY","METHOD"})
 */
class Image implements TypeInterface
{
    /**
     * @var mixed
     */
    public $width;

    /**
     * @var mixed
     */
    public $height;

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [$this->width, $this->height];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return ImageHandler::TYPE;
    }
}
