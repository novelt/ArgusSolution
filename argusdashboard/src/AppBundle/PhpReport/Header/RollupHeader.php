<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 11/1/2016
 * Time: 9:25 AM
 */

namespace AppBundle\PhpReport\Header;

class RollupHeader
{
    const HEADER = 'Rollup';

    private $result = array();

    public function __construct()
    {
        $this->result['header'] = self::HEADER;
        $this->result['value'] = array();
        $this->result['value']['dataset'] = true ;
        $this->result['value']['columns'] = array() ;
    }

    public function addRollupColumn($column, $value)
    {
        $this->result['value']['columns'][$column] = $value ;
    }

    public function addRollupPercentColumn($column, $dividend, $divisor, $decimalNumber = 2)
    {
        $this->result['value']['columns'][$column] =
            sprintf("{{ (row['%2\$s']['sum'] != 0 ? (row['%1\$s']['sum'] * 100 / row['%2\$s']['sum']) : 0) | number_format (%3\$s, '.', '') }}",
                $dividend,
                $divisor,
                $decimalNumber
            );
    }

    public function addDefaultSum()
    {
        $this->result['value']['defaultSum'] = true ;
    }

    public function getHeader()
    {
        return $this->result;
    }
}
