<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 2/10/2016
 * Time: 5:33 PM
 */

class Constant
{
    const SMS_SEPARATOR = ',';
    const SMS_SPACE = ' ';

    const TEMPLATE_WEEKLY = 'TW';
    const TEMPLATE_MONTHLY = 'TM';
    const TEMPLATE_ALERT = 'TA';
    const TEMPLATE_CONF = 'CF';

    // Period
    const PERIOD_WEEKLY = 'Weekly';
    const PERIOD_MONTHLY = 'Monthly';
    const PERIOD_NONE = 'None';
    const PERIOD_ALL = 'ALL';

    // Label Health Facility Name
    const KEYWORD_HEALTHFACILITY_LABEL = 'LBL';

    //Data Type Truncated
    const TYPE_TRUNCATED_STRING = 'Str';
    const TYPE_TRUNCATED_INTEGER = 'Int';
    const TYPE_TRUNCATED_DATE = 'Dat';

    // Data Type
    const TYPE_INTEGER = 'Integer';
    const TYPE_STRING = 'String';
    const TYPE_DATE = 'Date';

    // Operator
    const OPERATORS = array(
                            'GREATER'           => '>',
                            'GREATER_EQUAL'     => '>=',
                            'LESS'              => '<',
                            'LESS_EQUAL'        => '<=',
                            'NOT_EQUAL'         => '!='
                            );

    const SPECIAL_SMS_CARACTERS = '/[\'ÂâÁáÃãᾹᾱÇçČčĆćÊêËëĖėÎîÍíńÔôÓóÕõŒœŌōŚśŠšÛûŪūӰӱ]/';

    /**
     * @param $type
     * @return string
     *
     * Get Truncated type based on $type
     */
    static function GetTruncatedType($type)
    {
        $upperType = strtoupper($type);

        switch($upperType)
        {
            case strtoupper(Constant::TYPE_INTEGER):
                return Constant::TYPE_TRUNCATED_INTEGER;
                break ;
            case strtoupper(Constant::TYPE_STRING):
                return Constant::TYPE_TRUNCATED_STRING;
                break ;
            case strtoupper(Constant::TYPE_DATE):
                return Constant::TYPE_TRUNCATED_DATE;
                break ;
            default :
                return '' ;
        }
    }

    /**
     * @param $operator
     * @return string
     *
     * Get Operator based on $operator
     */
    static function GetOperatorFromString($operator)
    {
        $upperOperator =  strtoupper($operator);

        if (array_key_exists($upperOperator,Constant::OPERATORS )){
            return Constant::OPERATORS[$upperOperator];
        }

        return '';
    }

    /**
     * @param $message
     * @return bool
     *
     * Return true if $message contains one or more special characters in string SPECIAL_SMS_CARACTERS
     */
    static function existSpecialCharacter($message)
    {
        if (preg_match(self::SPECIAL_SMS_CARACTERS, $message))
        {
            return true;
        }

        return false ;
    }
}