<?php

/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 15/01/2018
 * Time: 11:54
 */
namespace AppBundle\Repository\Gateway;

use AppBundle\Entity\Gateway\GatewayQueue;
use AppBundle\Entity\Messages\IncomingSms;
use AppBundle\Entity\SesDashboardContact;
use AppBundle\Entity\SesDashboardIndicatorDimDate;
use AppBundle\Repository\BaseRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\Expr;

class GatewayQueueRepository extends BaseRepository
{
    /**
     * Get the latest Message received ever
     *
     * @param $phoneNumber
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastMessageSentEver($phoneNumber)
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->where('g.phoneNumber = :phoneNumber')
            ->setParameter('phoneNumber', $phoneNumber);

        $qb
            ->orderBy('g.sent', 'DESC');

        $qb
            ->setMaxResults(1);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the number of messages sent between $from and $to
     *
     * @param $phoneNumber
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getNumberOfMessageSent($phoneNumber, $from, $to)
    {
        $qb = $this->createQueryBuilder('g')
                ->select('COUNT(g.id)');

        $qb
            ->where('g.phoneNumber = :phoneNumber')
            ->setParameter('phoneNumber', $phoneNumber);

        $qb
            ->andWhere('g.sent BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to);

        $qb
            ->setMaxResults(1);

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    /**
     * Return sites joined with contacts and the SMS they sent
     * Note this does not return the generated SMS (ie: ArgusSMSGenerated)
     *
     * @param number[] $siteIds
     * @param array $start
     * @param array $end
     * @return array
     */
    public function getWeekGatewaySMSTraffic($siteIds, $start, $end)
    {

        // retrieve gateway ids from gateway queue
        $starttime = microtime(true);
        $outGateways = $this->_em->createQueryBuilder()
            ->select('g.gatewayId')
            ->from(GatewayQueue::class, 'g')
            ->innerJoin(
                SesDashboardContact::class,
                'c',
                Expr\Join::WITH,
                'c.phoneNumber = g.phoneNumber'
                . ' AND g.gatewayId NOT IN (\'\', \'ArgusSMSGenerated\')'
                . ' AND c.FK_SiteId IN (:siteIds)'
            )
            ->setParameter('siteIds', $siteIds)
            ->groupBy('g.gatewayId')
            ->getQuery();
        $outGatewayIds = $outGateways->getArrayResult();
        $end1 = microtime(true) - $starttime;

        // retrieve gateway ids from incoming sms
        $inGateways = $this->_em->createQueryBuilder()
            ->select('sms.gatewayId')
            ->from(IncomingSms::class, 'sms')
            ->innerJoin(
                SesDashboardContact::class,
                'c',
                Expr\Join::WITH,
                'c.phoneNumber = sms.phoneNumber'
                . ' AND sms.gatewayId NOT IN (\'\', \'ArgusSMSGenerated\')'
                . ' AND c.FK_SiteId IN (:siteIds)'
            )
            ->setParameter('siteIds', $siteIds)
            ->groupBy('sms.gatewayId')
            ->getQuery();
        $inGatewayIds = $inGateways->getArrayResult();

        // merge retrieved gateway ids
        $getGatewayIdFromResult = function ($r) {
            return $r['gatewayId'];
        };
        $gatewayIds = array_unique(
            array_merge(
                array_map($getGatewayIdFromResult, $inGatewayIds),
                array_map($getGatewayIdFromResult, $outGatewayIds)
            )
        );

        if (empty($gatewayIds)) {
            return [];
        }

        $selectGateways = implode(' UNION ALL SELECT ', array_map(
            function ($gateway) {
                return "'$gateway' gatewayId";
            },
            $gatewayIds
        ));

        $indicatorDimDateMetadata = $this->_em->getClassMetadata(SesDashboardIndicatorDimDate::class);
        $dimDateEpiWeekOfYear = $indicatorDimDateMetadata->getColumnName('epiWeekOfYear');
        $dimDateEpiYear = $indicatorDimDateMetadata->getColumnName('epiYear');
        $dimDateFullDate = $indicatorDimDateMetadata->getColumnName('fullDate');

        $gatewayQueueMetaData = $this->_em->getClassMetadata(GatewayQueue::class);

        // Outbound SMS stats
        // We use native SQL query because doctrine, to my knowledge, isn't
        // able to generate a query that joins on a
        // "select blabla union all blabla" subquery
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('week', 'week');
        $rsm->addScalarResult('year', 'year');
        $rsm->addScalarResult('gateway', 'gateway');
        $rsm->addScalarResult('totalOutboundSMS', 'totalOutboundSMS');
        $outboundQuery = $this->_em->createNativeQuery(
            'SELECT ' . $dimDateEpiWeekOfYear . ' week,'
            . '     ' . $dimDateEpiYear . ' year,'
            . '     gateways.gatewayId gateway,'
            . '     COUNT(DISTINCT smsOut.' . $gatewayQueueMetaData->getColumnName('id') . ') totalOutboundSMS'
            . ' FROM ' . $indicatorDimDateMetadata->getTableName() . ' dates'
            . ' JOIN (SELECT ' . $selectGateways . ') gateways'
            . '     ON ' . $dimDateEpiWeekOfYear . '>= :startWeek'
            . '         AND ' . $dimDateEpiYear . '>= :startYear'
            . '         AND ' . $dimDateEpiWeekOfYear . '<= :endWeek'
            . '         AND ' . $dimDateEpiYear . '<= :endYear'
            . ' LEFT JOIN ' . $gatewayQueueMetaData->getTableName() . ' smsOut'
            . '     ON smsOut.' . $gatewayQueueMetaData->getColumnName('creationDay') . ' = dates.' . $dimDateFullDate
            . '         AND smsOut.' . $gatewayQueueMetaData->getColumnName('gatewayId') . ' = gateways.gatewayId'
            . ' GROUP BY week, year, gateway'
            . ' ORDER BY week, year, gateway',
            $rsm
        )
            ->setParameters([
                'startWeek' => $start['Week'],
                'startYear' => $start['Year'],
                'endWeek' => $end['Week'],
                'endYear' => $end['Year'],
            ]);
        $outboundSMSresults = $outboundQuery->getresult();

        // Inbound SMS stats
        $incomingSMSMetaData = $this->_em->getClassMetadata(IncomingSms::class);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('week', 'week');
        $rsm->addScalarResult('year', 'year');
        $rsm->addScalarResult('gateway', 'gateway');
        $rsm->addScalarResult('totalInboundSMS', 'totalInboundSMS');
        $inboundQuery = $this->_em->createNativeQuery(
            'SELECT ' . $dimDateEpiWeekOfYear . ' week,'
            . '     ' . $dimDateEpiYear . ' year,'
            . '     gateways.gatewayId gateway,'
            . '     COUNT(DISTINCT smsIn.' . $incomingSMSMetaData->getColumnName('id') . ') totalInboundSMS'
            . ' FROM ' . $indicatorDimDateMetadata->getTableName() . ' dates'
            . ' JOIN (SELECT ' . $selectGateways . ') gateways'
            . '     ON ' . $dimDateEpiWeekOfYear . '>= :startWeek'
            . '         AND ' . $dimDateEpiYear . '>= :startYear'
            . '         AND ' . $dimDateEpiWeekOfYear . '<= :endWeek'
            . '         AND ' . $dimDateEpiYear . '<= :endYear'
            . ' LEFT JOIN ' . $incomingSMSMetaData->getTableName() . ' smsIn'
            . '     ON smsIn.' . $incomingSMSMetaData->getColumnName('creationDay') . ' = dates.' . $dimDateFullDate
            . '         AND smsIn.' . $incomingSMSMetaData->getColumnName('gatewayId') . ' = gateways.gatewayId'
            . ' GROUP BY week, year, gateway'
            . ' ORDER BY week, year, gateway',
            $rsm
        )
            ->setParameters([
                'startWeek' => $start['Week'],
                'startYear' => $start['Year'],
                'endWeek' => $end['Week'],
                'endYear' => $end['Year'],
            ]);
        $inboundSMSresults = $inboundQuery->getresult();

        // We ASSUME inbound and outbound queries returned the same rows 'week', 'year' and 'gateway'.
        // We merge them by index

        $results = $inboundSMSresults;
        foreach ($outboundSMSresults as $i => $out) {
            $results[$i]['totalOutboundSMS'] = $out['totalOutboundSMS'];
        }

        return $results;
    }
}