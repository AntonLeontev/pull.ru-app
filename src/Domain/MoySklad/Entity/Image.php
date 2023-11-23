<?php

namespace Src\Domain\MoySklad\Entity;

use App\Traits\Makeable;
use JsonSerializable;

class Image implements JsonSerializable
{
    use Makeable;

    public readonly string $filename;

    public readonly string $content;

    public function __construct(string $filename, string $path)
    {
        $this->filename = $filename;
        $this->content = base64_encode(file_get_contents($path));
    }

    public function jsonSerialize(): mixed
    {
        return [
            'filename' => $this->filename,
            'content' => $this->content,
        ];
    }
}
