<?php

declare(strict_types = 1);

namespace Genesis\PdfMerger\Source;

use Genesis\PdfMerger\PagesInterface;

interface SourceInterface
{
    /**
     * Get name of file or source
     */
    public function getName(): string;

    /**
     * Get pdf content
     */
    public function getContents(): string;

    /**
     * Get pages to fetch from source
     */
    public function getPages(): PagesInterface;
}
