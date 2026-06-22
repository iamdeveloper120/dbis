<?php

namespace App\Services\ProbeSetProcessing;

interface ProbeSetProcessingServiceInterface
{
    public function process(array $data, array $collectedData): array;
}
