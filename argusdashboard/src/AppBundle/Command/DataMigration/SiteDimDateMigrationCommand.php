<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 26/03/2018
 * Time: 14:35
 *
 * To debug with the terminal , enable PGP debug like this : set XDEBUG_CONFIG="idekey=PHPSTORM"
 */

namespace AppBundle\Command\DataMigration;

use AppBundle\Command\BaseCommand;
use AppBundle\Entity\SesDashboardSite;
use AppBundle\Entity\SesDashboardSiteRelationShip;
use AppBundle\Services\DimDate\IDimDateService;
use AppBundle\Services\SiteService;
use AppBundle\Utils\Parser;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrate Site Relation Ship data to fill DimDates fields
 *
 * Class SiteDimDateMigrationCommand
 * @package AppBundle\Command\DataMigration
 */
class SiteDimDateMigrationCommand extends BaseCommand
{
    /** @var SiteService $siteService*/
    private $siteService;

    /** @var IDimDateService $dimDateService */
    private $dimDateService;


    public function __construct(Logger $logger,
                                Parser $parser,
                                SiteService $siteService,
                                IDimDateService $dimDateService)
    {
        parent::__construct($logger, $parser);

        $this->siteService = $siteService;
        $this->dimDateService = $dimDateService;
    }

    protected function configure()
    {
        $this
            ->setName('migration:fillDimDates')
            ->setDescription('Fill all Dim Date fields in Site Relation Ship entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allSites = $this->siteService->getAll();

        /** @var SesDashboardSite $site */
        foreach ($allSites as $site) {

            $message = sprintf('Site [%s]', $site->getName());
            $output->writeln($message);
            $this->logger->info($message);

            /** @var SesDashboardSiteRelationShip $relationShip */
            foreach ($site->getSitesRelationShip() as $relationShip) {

                if ($relationShip->getDimDateFrom() != null ) {
                    if ($relationShip->getWeekDimDateFrom() == null) {
                        $weekDimDateFrom = $this->dimDateService->getWeekDimDateFrom($relationShip->getDimDateFrom());
                        $relationShip->setWeekDimDateFrom($weekDimDateFrom);
                    }

                    if ($relationShip->getMonthDimDateFrom() == null) {
                        $monthDimDateFrom = $this->dimDateService->getMonthDimDateFrom($relationShip->getDimDateFrom());
                        $relationShip->setMonthDimDateFrom($monthDimDateFrom);
                    }
                }

                if ($relationShip->getDimDateTo() != null ) {
                    if ($relationShip->getWeekDimDateTo() == null) {
                        $weekDimDateTo = $this->dimDateService->getWeekDimDateTo($relationShip->getDimDateTo());
                        $relationShip->setWeekDimDateTo($weekDimDateTo);
                    }

                    if ($relationShip->getMonthDimDateTo() == null) {
                        $monthDimDateTo = $this->dimDateService->getMonthDimDateTo($relationShip->getDimDateTo());
                        $relationShip->setMonthDimDateTo($monthDimDateTo);
                    }
                }

                $this->siteService->saveChanges($relationShip);
            }
        }
    }
}