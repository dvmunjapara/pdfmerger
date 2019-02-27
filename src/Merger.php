<?php

declare(strict_types = 1);

namespace Genesis\PdfMerger;

use Genesis\PdfMerger\Driver\DriverInterface;
use Genesis\PdfMerger\Driver\DefaultDriver;
use Genesis\PdfMerger\Source\SourceInterface;
use Genesis\PdfMerger\Source\FileSource;
use Genesis\PdfMerger\Source\RawSource;

/**
 * Merge existing pdfs into one
 *
 * Note that your PDFs are merged in the order that you add them
 */
final class Merger
{
    /**
     * @var SourceInterface[] List of pdf sources to merge
     */
    private $sources = [];

    /**
     * @var DriverInterface
     */
    private $driver;

    public function __construct(DriverInterface $driver = null)
    {
        $this->driver = $driver ?: new DefaultDriver;
    }

    /**
     * Add raw PDF from string
     */
    public function addRaw(string $content, PagesInterface $pages = null): void
    {
        $this->sources[] = new RawSource($content, $pages);
    }

    /**
     * Add PDF from file
     */
    public function addFile(string $filename, PagesInterface $pages = null): void
    {
        try {
            $this->sources[] = new FileSource($filename, $pages);
        } catch (Exception $e) {
        }
    }

    /**
     * Add files using iterator
     *
     * Note that optional pages constraint is used for every added pdf
     */
    public function addIterator(iterable $iterator, PagesInterface $pages = null): void
    {
        foreach ($iterator as $filename) {
            $this->addFile($filename, $pages);
        }
    }

    /**
     * Merges loaded PDFs
     */
    public function merge($destination,$output_mode = "file"): string
    {
        return $this->driver->merge($destination,$output_mode, ...$this->sources);
    }

    /**
     * Get page counts
     */
    public function pageCount(): int
    {
        return $this->driver->pageCount(...$this->sources);
    }

    /**
     * Reset internal state
     */
    public function reset(): void
    {
        $this->sources = [];
    }
}
