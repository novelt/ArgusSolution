<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/1/2016
 * Time: 9:25 AM
 */

namespace AppBundle\PhpReport\Header;

class ChartHeader
{
    const HEADER = 'Chart';

    const COLUMN_CHART = 'ColumnChart';
    const LINE_CHART = 'LineChart';

    /* DashStyle for Line Chart */
    const LINE_DASHSTYLE_SOLID = 'Solid';
    const LINE_DASHSTYLE_DASH = 'Dash';
    const LINE_DASHSTYLE_SHORT_DASH = 'ShortDash';
    const LINE_DASHSTYLE_SHORT_DOT = 'ShortDot';
    const LINE_DASHSTYLE_SHORT_DASH_DOT = 'ShortDashDot';
    const LINE_DASHSTYLE_SHORT_DASH_DOT_DOT = 'ShortDashDotDot';
    const LINE_DASHSTYLE_DOT = 'Dot';
    const LINE_DASHSTYLE_LONG_DASH = 'LongDash';
    const LINE_DASHSTYLE_DASH_DOT = 'DashDot';
    const LINE_DASHSTYLE_LONG_DASH_DOT = 'LongDashDot';
    const LINE_DASHSTYLE_LONG_DASH_DOT_DOT = 'LongDashDotDot';

    const REGEX_CLASS_NAME = '/[\s?!@#$%^&*()_=+,.<>\'":;\[\]\/|~`{\\}]/i';
    const CLASS_NAME_CHAR_REPLACEMENT = '-';

    private $result = [];
    private $type;

    public function __construct($type, $title)
    {
        $this->type = $type;

        $this->result['header'] = self::HEADER;
        $this->result['value'] = [];
        $this->result['value']['dataset'] = true ;
        $this->result['value']['columns'] = [] ;
        $this->result['value']['type'] = $this->type ;
        $this->result['value']['title'] = $title ;
        $this->result['value']['options'] = [] ;
        $this->result['value']['options']['hAxis'] = [] ;
        $this->result['value']['options']['vAxis'] = [] ;
    }

    public function setSubTitle($subTitle)
    {
        $this->result['value']['subtitle'] = $subTitle ;
    }

    public function addColumn($column, $dashStyle = self::LINE_DASHSTYLE_SOLID)
    {
        $this->result['value']['columns'][] = $column ;

        if ($this->type == self::LINE_CHART) {
            $this->result['value']['dashStyle'][] = $dashStyle;
            $this->result['value']['options']['className'][] = $this->getCssClassNameFromColumnName($column);
        }
    }

    /**
     * This is specific for C3 charts.
     * @param $columnName
     * @return string
     */
    static function getCssClassNameFromColumnName($columnName)
    {
        $stringAsArray = str_split($columnName);
        $i = 0;

        while($i < sizeof($stringAsArray)) {
            if(preg_match(ChartHeader::REGEX_CLASS_NAME, $stringAsArray[$i])) {
                $stringAsArray[$i] = ChartHeader::CLASS_NAME_CHAR_REPLACEMENT;
            }
            $i++;
        }
        return implode('', $stringAsArray);
    }

    public function setAbscissaTitle($title)
    {
        $this->result['value']['options']['hAxis']['title'] = $title;
    }

    public function setOrdinateTitle($title)
    {
        $this->result['value']['options']['vAxis']['title'] = $title;
    }

    public function setOrdinateMinValue($minValue)
    {
        $this->result['value']['options']['vAxis']['minValue'] = $minValue;
    }

    public function setOrdinateMaxValue($maxValue)
    {
        $this->result['value']['options']['vAxis']['maxValue'] = $maxValue;
    }

    public function setColumnToLine($value)
    {
        $this->result['value']['columnToLine'] = $value ;
    }

    public function setEnableLabels($bool)
    {
        $this->result['value']['enabledLabels'] = $bool ;
    }

    public function enabledDashStyle()
    {
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_SOLID ; // First is always bypassed if Line Chart
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_SOLID ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_DASH ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_SHORT_DASH ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_SHORT_DOT ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_SHORT_DASH_DOT ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_SHORT_DASH_DOT_DOT ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_DOT ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_LONG_DASH ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_DASH_DOT ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_LONG_DASH_DOT ;
        $this->result['value']['dashStyle'][] = self::LINE_DASHSTYLE_LONG_DASH_DOT_DOT ;
    }

    public function getHeader()
    {
        return $this->result;
    }
}