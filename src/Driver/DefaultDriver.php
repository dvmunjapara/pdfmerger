<?php

declare(strict_types = 1);

namespace Genesis\PdfMerger\Driver;

use Genesis\PdfMerger\Source\SourceInterface;

final class DefaultDriver implements DriverInterface
{
    /**
     * @var DriverInterface
     */
    private $wrapped;

    public function __construct(DriverInterface $wrapped = null)
    {
        $this->wrapped = $wrapped ?: new Fpdi2Driver;
    }

    public function merge($destination,$output_mode,SourceInterface ...$sources): string
    {
        return $this->wrapped->merge($destination,$output_mode,...$sources);
    }

    public function pageCount(SourceInterface ...$sources): int
    {
        dd($sources);
        return $this->wrapped->pageCount($sources);
    }
}
