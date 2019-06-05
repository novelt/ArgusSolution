<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 09/08/2016
 * Time: 12:00
 */

namespace AppBundle\Utils\Response;

use Symfony\Component\HttpFoundation\Response;

class CsvResponse extends Response
{
    const UTF8_BOM = "\xEF\xBB\xBF";

    protected $data;
    protected $filename = 'export.csv';
    protected $delimiter = ',';

    public function __construct($data = array(), $status = 200, $headers = array(), $delimiter = ',', $firstRowIsHeader = false, $addExcelCompatibility = false)
    {
        parent::__construct('', $status, ($firstRowIsHeader ? $data[0]:$headers));
        $this->delimiter = $delimiter;
        $this->setData($data, $addExcelCompatibility);
    }

    public function setData(array $data, $addExcelCompatibility = false)
    {
        $output = fopen('php://temp', 'r+');

        if($addExcelCompatibility) {
            fputcsv($output, ["sep=".$this->delimiter], $this->delimiter);
        }

        foreach ($data as $row) {
            fputcsv($output, $row, $this->delimiter);
        }
        rewind($output);

        $this->data = self::UTF8_BOM;
        while ($line = fgets($output)) {
            $this->data .= $line;
        }
        $this->data .= fgets($output);
        return $this->update();
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this->update();
    }

    protected function update()
    {
        $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $this->filename));
        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/csv');
        }
        return $this->setContent($this->data);
    }
}