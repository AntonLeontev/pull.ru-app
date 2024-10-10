<?php

namespace App\Support;

class SmsMessage
{
    public ?string $from = null;

    public string $to;

    public string $text;

    public function from(string $from)
    {
        $this->from = $from;

        return $this;
    }

    public function to(string $to)
    {
        $this->to = $to;

        return $this;
    }

    public function text(string $content)
    {
        $this->text = $content;

        return $this;
    }
}
