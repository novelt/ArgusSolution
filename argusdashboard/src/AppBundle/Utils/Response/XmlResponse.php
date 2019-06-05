<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 09/08/2016
 * Time: 12:01
 */

namespace AppBundle\Utils\Response;

use Symfony\Component\HttpFoundation\Response;


class XmlResponse extends Response
{
    protected $data;
    protected $filename = 'export.xml';

    public function __construct($data = '', $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);
        $this->data = $data;
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
            $this->headers->set('Content-Type', 'xml');
        }
        return $this->setContent($this->data);
    }
}