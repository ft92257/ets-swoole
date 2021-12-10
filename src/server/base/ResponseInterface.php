<?php

namespace Ets\server\base;


interface ResponseInterface
{
    public function finish(string $output);

    public function getOutput(): string;
}