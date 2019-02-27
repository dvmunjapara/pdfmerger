<?php

declare(strict_types = 1);

namespace Genesis\PdfMerger\Driver;

use Genesis\PdfMerger\Exception;
use Genesis\PdfMerger\Source\SourceInterface;
use setasign\Fpdi\Fpdi as FpdiFpdf;
use setasign\Fpdi\Tcpdf\Fpdi as FpdiTcpdf;
use setasign\Fpdi\PdfParser\StreamReader;

final class Fpdi2Driver implements DriverInterface
{
    /**
     * @var FpdiFpdf|FpdiTcpdf
     */
    private $fpdi;

    /**
     * @param FpdiFpdf|FpdiTcpdf $fpdi
     */
    public function __construct($fpdi = null)
    {
        $this->fpdi = $fpdi ?: new FpdiTcpdf;

        if (!($this->fpdi instanceof FpdiFpdf) && !($this->fpdi instanceof FpdiTcpdf)) {
            throw new \InvalidArgumentException('Constructor argument must be an FPDI instance.');
        }
    }

    public function merge($destination,$output_mode,SourceInterface ...$sources): string
    {
        $sourceName = '';

        try {
            $fpdi = clone $this->fpdi;

            foreach ($sources as $source) {
                $sourceName = $source->getName();
                $pageCount = $fpdi->setSourceFile(StreamReader::createByString($source->getContents()));
                $pageNumbers = $source->getPages()->getPageNumbers() ?: range(1, $pageCount);

                foreach ($pageNumbers as $pageNr) {
                    $template = $fpdi->importPage($pageNr);
                    $size = $fpdi->getTemplateSize($template);
                    $fpdi->SetPrintHeader(false);
                    $fpdi->SetPrintFooter(false);
                    $fpdi->AddPage(
                        $size['width'] > $size['height'] ? 'L' : 'P',
                        [$size['width'], $size['height']]
                    );
                    $fpdi->useTemplate($template);
                }
            }

            $mode = $this->_switchmode($output_mode);
            return $fpdi->Output($destination, $mode);
        } catch (\Exception $e) {
            throw new Exception("'{$e->getMessage()}' in '$sourceName'", 0, $e);
        }
    }

    public function pageCount(SourceInterface ...$sources): int
    {
        try {

            $fpdi = clone $this->fpdi;
            $pageCount = 0;


            foreach ($sources as $source) {
                $sourceName = $source->getName();
                $pageCount += $fpdi->setSourceFile(StreamReader::createByString($source->getContents()));
            }

            return $pageCount;

        } catch (\Exception $e) {

            throw new Exception("'{$e->getMessage()}' in '$sourceName'", 0, $e);
        }
    }

    /**
     * FPDI uses single characters for specifying the output location. Change our more descriptive string into proper format.
     * @param $mode
     * @return string
     */
    private function _switchmode($mode)
    {
        switch (strtolower($mode)) {
            case 'download':
                return 'D';
                break;
            case 'browser':
                return 'I';
                break;
            case 'file':
                return 'F';
                break;
            case 'string':
                return 'S';
                break;
            default:
                return 'I';
                break;
        }
    }
}
