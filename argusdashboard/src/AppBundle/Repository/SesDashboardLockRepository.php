<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 20-Jul-18
 * Time: 19:56
 */

namespace AppBundle\Repository;

class SesDashboardLockRepository extends BaseRepository
{
    /**
     * @param null $name
     * @param null $value
     * @param null $expire
     * @param null $hydrationMode
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneLock($name = null, $value = null, $expire = null, $hydrationMode = null) {
        $qb = $this->getLockQueryBuilder($name, $value, $expire);

        return $qb->getQuery()->getOneOrNullResult($hydrationMode);
    }

    /**
     * @param null $name
     * @param null $value
     * @param null $expire
     * @param null $hydrationMode
     * @return array
     */
    public function findLocks($name = null, $value = null, $expire = null, $hydrationMode = null) {
        $qb = $this->getLockQueryBuilder($name, $value, $expire);

        return $qb->getQuery()->getResult($hydrationMode);
    }

    /**
     * @param null $name
     * @param null $value
     * @param null $expire
     * @return mixed
     */
    public function removeExpiredLock($name=null, $value=null, $expire=null) {
        $qb = $this->getLockQueryBuilder($name, $value, null);

        if($expire !== null) {
            $qb->andWhere('l.expire <= :expire');
            $qb->setParameter('expire', $expire);
        }

        $qb->delete();

        return $qb->getQuery()->execute();
    }

    private function getLockQueryBuilder($name = null, $value = null, $expire = null) {
        $qb = $this->createQueryBuilder('l');

        $this->addWhere($qb, 'l', 'name', $name);
        $this->addWhere($qb, 'l', 'value', $value);

        return $qb;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->_entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->_entityName = $entityName;
    }
}