<?php
/**
 * Created by PhpStorm.
 * User: eotin
 * Date: 18/08/2016
 * Time: 09:54
 */

namespace AppBundle\Repository;

use AppBundle\Entity\BaseEntity;
use AppBundle\Services\DbConstant;
use AppBundle\Services\Exception\DatabaseException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use AppBundle\Entity\Constant;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class BaseRepository
 * Provide functions used by all repository classes like filter functions
 * TODO
 *
 * @package AppBundle\Repository
 */
class BaseRepository extends EntityRepository implements RepositoryInterface
{
    const FULL_REPORT_ALIAS = "fr";
    const PART_REPORT_ALIAS = "pr";
    const REPORT_ALIAS = "r";
    const REPORT_VALUES_ALIAS = "rv";
    const SITE_RELATION_SHIP_ALIAS = "srs";

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var string
     */
    protected $entityManagerName;

    /**
     * Begin Transaction
     */
    public function beginTransaction()
    {
        $this->_em->beginTransaction();
    }

    /**
     * Commit Transaction
     */
    public function commit()
    {
        $this->_em->commit();
    }

    /**
     * Rollback Transaction
     */
    public function rollback()
    {
        $this->_em->rollback();
    }

    /**
     * Tells Doctrine you want to (eventually) save the entity (no queries yet)
     * @param $entity
     */
    public function persist($entity) {
        $this->_em->persist($entity);
    }

    /**
     * Tells Doctrine that you'd like to remove the given entity from the database (no queries yet)
     * @param $entity
     */
    public function remove($entity) {
        $this->_em->remove($entity);
    }

    /**
     * @param $entity
     */
    public function refresh($entity)
    {
        $this->_em->refresh($entity);
    }

    public function resetConnection() {
        if($this->doctrine !== null) {
            $this->setEntityManager($this->doctrine->resetManager($this->entityManagerName));
        }
    }

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em) {
        $this->_em = $em;
    }

    /**
     * Actually executes the queries (i.e. the INSERT query)
     * @param null $entity
     * @throws DatabaseException
     */
    public function saveChanges($entity = null, $detach = false) {
        try {
            $this->_em->flush($entity);

            if ($detach && $entity !== null) {
                $this->_em->detach($entity);
            }
        }
        catch(\Exception $e) {
            throw new DatabaseException(sprintf("Error when saving changes in the database: %s", $e->getMessage()), $e->getCode(), $e);
        }
    }

    /**
     * Returns the name in the database of the entity class attached to this repository instance
     * @return string|null
     */
    public function getEntityTableName() {
        $classMetadata = $this->_em->getClassMetadata($this->_entityName);

        if($classMetadata !== null) {
            $table = $classMetadata->table;
            if($table !== null && is_array($table) && array_key_exists('name', $table)) {
                return $table['name'];
            }
        }

        return null;
    }

    /**
     * Empty the table
     * @param bool $autocommit If false, you will need to flush to execute the request. Else, the table will be directly emptied
     * @return mixed
     */
    public function emptyTable($autocommit) {
        if($autocommit) {
            $qb = $this->createQueryBuilder('e')->delete();
            return $qb->getQuery()->execute();
        }
        else {
            foreach($this->findAll() as $entity) {
                $this->remove($entity);
            }
        }
    }

    /**
     * Truncate the table
     */
    public function truncateTable() {
        $cmd = $this->_em->getClassMetadata($this->getClassName());
        $connection = $this->_em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function lockTable()
    {
        throw new NotImplementedException("To be able to lock the table, you must override and implement the method lockTable() in the repository");
    }

    public function unlockTable()
    {
        $this->_em->getConnection()->exec('UNLOCK TABLES;');
    }

        /**
     * Returns true if the table is not empty.
     * Warning: the entity must have its primary key named "id"
     * @return bool
     */
    public function any() {
        $qb = $this->createQueryBuilder('e')->select('e.id')->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result !== null && sizeof($result) > 0;
    }

    /**
     * @return BaseEntity|null
     */
    public function getLatestSavedEntity() {
        $qb = $this->getLatestSavedEntityQueryBuilder();

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return QueryBuilder
     */
    protected function getLatestSavedEntityQueryBuilder()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->orderBy('e.id', 'DESC');
        $qb->setMaxResults(1);

        return $qb;
    }

    /**
     * @param $id
     * @return BaseEntity|null
     */
    public function findOneById($id) {
        $qb = $this->createQueryBuilder('e');

        $this->addWhere($qb,'e', 'id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Add Where status on fullReport 'fr'
     *
     * @param QueryBuilder $qb
     * @param $display
     */
    protected function whereStatus(QueryBuilder $qb, $display)
    {
        if (null === $display)
        {
            $display = Constant::STATUS_PENDING;
        }

        if ($display == Constant::STATUS_PENDING)
        {
            $qb
                ->andWhere('fr.status = :pending OR fr.status = :rejectedFromAbove OR fr.status is null OR fr.status = \'\' ')
                ->setParameter('pending', Constant::STATUS_PENDING)
                ->setParameter('rejectedFromAbove', Constant::STATUS_REJECTED_FROM_ABOVE)
            ;
        }
        else if ($display == Constant::STATUS_ALL)
        {
            // NO Filters
        }
        else
        {
            $qb
                ->andWhere('fr.status = :display')
                ->setParameter('display', $display)
            ;
        }
    }

    /**
     * Add Where startDate & endDate on fullReport 'fr'
     *
     * @param QueryBuilder $qb
     * @param $startDate
     * @param $endDate
     */
    protected function whereTime(QueryBuilder $qb, $startDate, $endDate)
    {
        if ($startDate != null && $endDate != null) {
            $qb
                ->andWhere('fr.startDate BETWEEN :start AND :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate);
        }
    }

    /**
     * Add Where period  on fullReport 'fr'
     *
     * @param QueryBuilder $qb
     * @param $period
     */
    protected function wherePeriod(QueryBuilder $qb, $period)
    {
        if ($period != null) {
            $qb
                ->andWhere('fr.period = :period')
                ->setParameter('period', $period);
        }
    }


    /**
     * Add Where DimDate on sitesRelationShip 'srsC'
     *
     * @param QueryBuilder $qb
     * @param $dimDateFromId
     * @param $dimDateToId
     */
    protected function whereSiteDimDate(QueryBuilder $qb, $dimDateFromId, $dimDateToId)
    {
        if ($dimDateFromId != null) {
            $qb->andWhere('srsC.FK_DimDateFromId < :dimDateToId')
                ->setParameter('dimDateToId', $dimDateToId);
        }

        if ($dimDateToId != null) {
            $qb->andWhere('(srsC.FK_DimDateToId >= :dimDateFromId or srsC.FK_DimDateToId IS NULL)')
                ->setParameter('dimDateFromId', $dimDateFromId);
        } else {
            $qb->andWhere('srsC.FK_DimDateToId IS NULL');
        }
    }

    /**
     * Disable SQLLogger to improve performance (during bulk inserts for example)
     */
    public function disableSQLLogger()
    {
        $this->_em->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param $entity
     */
    public function detach($entity) {
        return $this->_em->detach($entity);
    }

    protected function formatStoredProcedureParams($input) {
        if($input === null || $input === DbConstant::NULL) {
            return 'null';
        }
        else if($input === false) {
            return 0;
        }
        else if($input === true) {
            return 1;
        }
        else if(is_array($input)) {
            if(sizeof($input) == 0) {
                return 'null';
            }
            else {
                $inputCopy = $input;
                if(($key = array_search(DbConstant::NULL, $input)) !== false) {
                    $inputCopy = $input;
                    $inputCopy[$key] = 'NULL';
                }

                return "'".join(', ', $inputCopy)."'";
            }
        }
        else if(is_string($input)) {
            return sprintf("'%s'", addslashes($input));
        }
        else if ($input instanceof \DateTime) {
            return sprintf("'%s'", $input->format("Y-m-d H:i:s"));
        }
        else {
            return $input;
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param $objectName
     * @param $propertyName
     * @param $value
     */
    protected function addWhere(QueryBuilder $qb, $objectName, $propertyName, $value) {
        if($value !== null) {
            if($value === DbConstant::NULL) {
                $qb->andWhere($objectName.'.'.$propertyName.' IS NULL');
            }
            else if($value === DbConstant::NOT_NULL) {
                $qb->andWhere($objectName.'.'.$propertyName.' IS NOT NULL');
            }
            else if(is_array($value)) {
                $qb->andWhere($objectName.'.'.$propertyName.' IN (:'.$propertyName.')');
                $qb->setParameter($propertyName, $value);
            }
            else {
                $qb->andWhere($objectName.'.'.$propertyName.' = :'.$propertyName);
                $qb->setParameter($propertyName, $value);
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param $objectName
     * @param $propertyName
     * @param $value
     */
    protected function addWhereNot(QueryBuilder $qb, $objectName, $propertyName, $value) {
        if($value !== null) {
            if($value === DbConstant::NULL) {
                $qb->andWhere($objectName.'.'.$propertyName.' IS NOT NULL');
            }
            else if($value === DbConstant::NOT_NULL) {
                $qb->andWhere($objectName.'.'.$propertyName.' IS NULL');
            }
            else if(is_array($value)) {
                $qb->andWhere($objectName.'.'.$propertyName.' NOT IN (:'.$propertyName.')');
                $qb->setParameter($propertyName, $value);
            }
            else {
                $qb->andWhere($objectName.'.'.$propertyName.' != :'.$propertyName);
                $qb->setParameter($propertyName, $value);
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param $objectName
     * @param $propertyName
     * @param $value
     */
    protected function addSet(QueryBuilder $qb, $objectName, $propertyName, $value) {
        if($value !== null) {
            if($value === DbConstant::NULL) {
                $qb->set($objectName.'.'.$propertyName, ':'.$propertyName);
                $qb->setParameter($propertyName, 'NULL');
            }
            else {
                $qb->set($objectName.'.'.$propertyName, ':'.$propertyName);
                $qb->setParameter($propertyName, $value);
            }
        }
    }

    /**
     * @return RegistryInterface
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param RegistryInterface $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @return string
     */
    public function getEntityManagerName()
    {
        return $this->entityManagerName;
    }

    /**
     * @param string $entityManagerName
     */
    public function setEntityManagerName($entityManagerName)
    {
        $this->entityManagerName = $entityManagerName;
    }
}