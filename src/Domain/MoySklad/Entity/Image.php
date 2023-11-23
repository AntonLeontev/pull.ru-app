<?php

namespace Src\Domain\MoySklad\Entity;

use App\Traits\Makeable;
use JsonSerializable;
use Src\Domain\MoySklad\Exceptions\ImageCreatingException;

class Image implements JsonSerializable
{
    use Makeable;

    public readonly string $filename;

    public readonly string $content;

    public function __construct(string $filename, string $content = '', string $url = '')
    {
        if (empty($content) && empty($url)) {
            throw new ImageCreatingException('Не переданы ни контент ни ссылка на изображение', 1);
        }

        $this->filename = $filename;

        if (empty($content)) {
            $this->content = base64_encode(file_get_contents($url));
        } else {
            $this->content = base64_encode($content);
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'filename' => $this->filename,
            'content' => $this->content,
        ];
    }
}
