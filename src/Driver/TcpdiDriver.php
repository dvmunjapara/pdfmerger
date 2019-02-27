<?php

declare(strict_types = 1);

namespace Genesis\PdfMerger\Driver;

use Genesis\PdfMerger\Exception;
use Genesis\PdfMerger\Source\SourceInterface;

final class TcpdiDriver implements DriverInterface
{
    /**
     * @var \TCPDI
     */
    private $tcpdi;

    public function __construct(\TCPDI $tcpdi = null)
    {
        $this->tcpdi = $tcpdi ?: new \TCPDI;
    }

    public function merge($destination,$output_mode,SourceInterface ...$sources): string
    {
        $sourceName = '';

        try {
            $tcpdi = clone $this->tcpdi;

            foreach ($sources as $source) {
                $sourceName = $source->getName();
                $pageCount = $tcpdi->setSourceData($source->getContents());
                $pageNumbers = $source->getPages()->getPageNumbers() ?: range(1, $pageCount);

                foreach ($pageNumbers as $pageNr) {
                    $template = $tcpdi->importPage($pageNr);
                    $size = $tcpdi->getTemplateSize($template);
                    $tcpdi->SetPrintHeader(false);
                    $tcpdi->SetPrintFooter(false);
                    $tcpdi->AddPage(
                        $size['w'] > $size['h'] ? 'L' : 'P',
                        [$size['w'], $size['h']]
                    );
                    $tcpdi->useTemplate($template);
                }
            }

            $mode = $this->_switchmode($output_mode);
            return $tcpdi->Output($destination, $mode);
        } catch (\Exception $e) {
            throw new Exception("'{$e->getMessage()}' in '$sourceName'", 0, $e);
        }
    }

    public function pageCount(SourceInterface ...$sources): int
    {
        try {

            $tcpdi = clone $this->tcpdi;
            $pageCount = 0;

            foreach ($sources as $source) {
                $pageCount += $tcpdi->setSourceData($source->getContents());
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
