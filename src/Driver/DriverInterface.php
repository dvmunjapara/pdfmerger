<?php

namespace Genesis\PdfMerger\Driver;

use Genesis\PdfMerger\Source\SourceInterface;

interface DriverInterface
{
    /**
     * Merge multiple sources
     */
    public function merge($destination,$output_mode,SourceInterface ...$sources): string;

    public function pageCount(SourceInterface ...$sources): int;
}
