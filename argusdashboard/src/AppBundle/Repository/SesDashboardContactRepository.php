<?php
/**
 * Contact Repository
 *
 * @author François Cardinaux, inspired by SesDashboardSiteRepository.php
 */

namespace AppBundle\Repository;

use AppBundle\Entity\SesDashboardContact;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\Constant;


class SesDashboardContactRepository extends BaseRepository
{
    /**
     * Return All contacts as array
     *
     * @return array
     */
    public function getAllContactsArray()
    {
        $qb = $this->createQueryBuilder('c', 'c.id')
            ->leftJoin('c.site','s')
            ->addSelect('s');
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param $id
     * @param $contactTypeId
     * @param $siteId
     * @param null $imei
     * @param null $imei2
     * @return SesDashboardContact[]
     */
    public function findContact($id, $contactTypeId, $siteId, $imei = null, $imei2 = null) {
        $qb = $this->getContactQueryBuilder($id, $contactTypeId, $siteId, $imei, $imei2);
        return $qb->getQuery()->getResult();
    }

    /**
     * Get contact and locations
     *
     * @param $id
     * @param $contactTypeId
     * @param $siteId
     * @param null $imei
     * @param null $imei2
     * @return SesDashboardContact[]
     */
    public function findContactWithLocations($id, $contactTypeId, $siteId, $imei = null, $imei2 = null) {
        $qb = $this->getContactQueryBuilder($id, $contactTypeId, $siteId, $imei, $imei2);

        $qb
            ->leftJoin('c.site', 's')
            ->addSelect('s');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param $contactTypeId
     * @param $siteId
     * @param null $imei
     * @param null $imei2
     * @return int[]
     */
    public function findContactIds($id, $contactTypeId, $siteId, $imei = null, $imei2 = null) {
        $qb = $this->getContactQueryBuilder($id, $contactTypeId, $siteId, null, null, array('id'));
        return array_map('current', $qb->getQuery()->getScalarResult());
    }

    /**
     * @param $id
     * @param $contactTypeId
     * @param $siteId
     * @param null $imei
     * @param null $imei2
     * @param $fields
     * @return QueryBuilder
     */
    private function getContactQueryBuilder($id, $contactTypeId, $siteId, $imei = null, $imei2 = null, $fields = null) {
        $qb = $this->createQueryBuilder('c');
        if($fields !== null && sizeof($fields) > 0) {
            $qb->select('c.'.join(', c.',$fields));
        }

        $this->addWhere($qb, 'c', 'id', $id);
        $this->addWhere($qb, 'c', 'contactTypeId', $contactTypeId);
        $this->addWhere($qb, 'c', 'FK_SiteId', $siteId);
        $this->addWhere($qb, 'c', 'imei', $imei);
        $this->addWhere($qb, 'c', 'imei2', $imei2);

        return $qb;
    }

    /**
     * @param null $imei
     * @return QueryBuilder
     */
    private function getContactByIMEIQueryBuilder($imei = null) {
        $qb = $this->createQueryBuilder('c');

        if($imei !== null) {
            if (is_array($imei)) {
                $qb->orWhere('c.imei IN (:imei)');
                $qb->orWhere('c.imei2 IN (:imei)');
                $qb->setParameter('imei', $imei);
            } else {
                $qb->orWhere('c.imei = :imei');
                $qb->orWhere('c.imei2 = :imei');
                $qb->setParameter('imei', $imei);
            }
        }

        return $qb;
    }

    /**
     * Retrieve contact by it's imei
     *
     * @param string $imei
     * @return SesDashboardContact[]
     */
    public function findByImeis($imei)
    {
        $qb = $this->getContactByIMEIQueryBuilder($imei);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $imei
     * @return SesDashboardContact
     */
    public function findOneByImeis($imei)
    {
        $qb = $this->getContactByIMEIQueryBuilder($imei);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function removeImeiFromContacts($imei, $exceptedUserId=null) {
        $qb = $this->createQueryBuilder('c');
        $qb->update();

        $qb->set("c.imei", ':imei_new_value');
        $qb->setParameter('imei_new_value', null);

        $this->addWhere($qb, 'c', 'imei', $imei);
        $this->addWhereNot($qb, 'c', 'id', $exceptedUserId);

        return $qb->getQuery()->execute();
    }
}