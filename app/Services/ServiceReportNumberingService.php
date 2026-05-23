<?php

namespace App\Services;

use App\Models\ReportSequence;

class ServiceReportNumberingService
{
    public function generate(string $type): string
    {
        return ReportSequence::nextNumber($type);
    }
}
