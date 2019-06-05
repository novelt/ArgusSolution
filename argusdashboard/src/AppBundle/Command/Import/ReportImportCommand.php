<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 23/06/2016
 * Time: 15:17
 */

namespace AppBundle\Command\Import;


use AppBundle\Services\ImportService;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReportImportCommand
 * @package AppBundle\Command\Import
 *
 * To debug with the terminal , enable PGP debug like this : set XDEBUG_CONFIG="idekey=PHPSTORM"
 */
class ReportImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('report:import')
            ->setDescription('Import new Xml reports');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ImportService $importService */
        $importService = $this->getContainer()->get('ImportService');
        $importService->importXmlReportFiles();
    }
}