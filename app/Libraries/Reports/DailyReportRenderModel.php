<?php

namespace App\Libraries\Reports;

class DailyReportRenderModel
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function tokenValues(): array
    {
        $values = [];
        foreach (DailyReportTokenMap::keys() as $key) {
            $values[$key] = isset($this->data[$key]) ? (string) $this->data[$key] : '';
        }

        return $values;
    }
}

