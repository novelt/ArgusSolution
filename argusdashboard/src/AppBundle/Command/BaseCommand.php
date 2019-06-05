<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 09/12/2016
 * Time: 11:36
 */

namespace AppBundle\Command;


use AppBundle\Utils\Parser;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct(Logger $logger, Parser $parser)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->parser = $parser;
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->logger->info(sprintf("%s: Received the following parameters: [%s]", get_class($this), $this->getParametersToString($input)));
    }

    protected function getService($serviceName) {
        return $this->getContainer()->get($serviceName);
    }

    protected function getApplicationParameter($paramName) {
        return $this->getContainer()->getParameter($paramName);
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @param \DateTime|null $defaultValue
     * @return \DateTime
     */
    protected function parseOptionToDateTime(InputInterface $input, $parameterName, \DateTime $defaultValue=null) {
        $optionValue = $input->getOption($parameterName);
        return $this->parseParameterToDateTime($optionValue, $defaultValue);
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @param null $defaultValue
     * @return int|null
     */
    protected function parseOptionToInteger(InputInterface $input, $parameterName, $defaultValue=null) {
        $optionValue = $input->getOption($parameterName);
        return $this->parseParameterToInteger($optionValue, $defaultValue);
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @param null $defaultValue
     * @return mixed|null
     */
    protected function parseOptionToBoolean(InputInterface $input, $parameterName, $defaultValue=null) {
        $optionValue = $input->getOption($parameterName);
        return $this->parseParameterToBoolean($optionValue, $defaultValue);
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @param \DateTime|null $defaultValue
     * @return \DateTime
     */
    protected function parseArgumentToDateTime(InputInterface $input, $parameterName, \DateTime $defaultValue=null) {
        $argumentValue = $input->getArgument($parameterName);
        return $this->parseParameterToDateTime($argumentValue, $defaultValue);
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @param null $defaultValue
     * @return int|null
     */
    protected function parseArgumentToInteger(InputInterface $input, $parameterName, $defaultValue=null) {
        $argumentValue = $input->getArgument($parameterName);
        return $this->parseParameterToInteger($argumentValue, $defaultValue);
    }

    /**
     * @param InputInterface $input
     * @param $parameterName
     * @param null $defaultValue
     * @return mixed|null
     */
    protected function parseArgumentToBoolean(InputInterface $input, $parameterName, $defaultValue=null) {
        $argumentValue = $input->getArgument($parameterName);
        return $this->parseParameterToBoolean($argumentValue, $defaultValue);
    }

    /**
     * @param null $param
     * @param \DateTime|null $defaultValue
     * @return \DateTime
     */
     protected function parseParameterToDateTime($param=null, \DateTime $defaultValue=null)
     {
         if ($param !== null) {
             $date = $this->parser->parseDate($param);

             if($date === null) {
                 throw new InvalidArgumentException(sprintf("The argument '%s' is invalid", $param));
             }

             return $date;
         }
         else {
             return $defaultValue;
         }
     }

    /**
     * @param null $param
     * @param null $defaultValue
     * @return int|null
     */
    protected function parseParameterToInteger($param=null, $defaultValue=null)
    {
        if($param !== null) {
            $int = $this->parser->parseInteger($param);

            if($int === null) {
                throw new InvalidArgumentException(sprintf("The argument '%s' is invalid", $param));
            }

            if ($int <= 0) {
                return null;
            }
            else {
                return $int;
            }
        }
        else {
            return $defaultValue;
        }
    }

    /**
     * @param null $param
     * @param null $defaultValue
     * @return mixed|null
     */
    protected function parseParameterToBoolean($param=null, $defaultValue=null)
    {
        if($param !== null) {
            $bool = $this->parser->parseBoolean($param);

            if($bool === null) {
                throw new InvalidArgumentException(sprintf("The argument '%s' is invalid", $param));
            }

            return $bool;
        }
        else {
            return $defaultValue;
        }
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    protected function getParametersToString(InputInterface $input) {
        $str = '';
        $inputOptions = $input->getOptions();

        if($inputOptions !== null) {
            $keys = array_keys($inputOptions);
            $separator = '';

            foreach($keys as $key) {
                $str .= sprintf("%s%s:[%s]", $separator, $key, $inputOptions[$key]);
                $separator = ', ';
            }
        }

        return $str;
    }

    protected function log($message, OutputInterface $output) {
        if($output !== null) {
            $output->writeln($message);
        }
        $this->logger->info($message);
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param Parser $parser
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
    }
}