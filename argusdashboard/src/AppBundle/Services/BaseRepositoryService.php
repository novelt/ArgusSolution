<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 21/11/2016
 * Time: 16:22
 */

namespace AppBundle\Services;


use AppBundle\Entity\Security\SesDashboardUser;
use AppBundle\Repository\BaseRepository;
use AppBundle\Repository\RepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

abstract class BaseRepositoryService extends BaseService implements RepositoryServiceInterface
{
    /**
     * @return array
     */
    public function findAll() {
        return $this->getRepository()->findAll();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findById($id) {
        return $this->getRepository()->findById($id);
    }

    public function findOneById($id) {
        return $this->getRepository()->findOneById($id);
    }
    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return mixed
     */
    public function find($id, $lockMode = null, $lockVersion = null) {
        return $this->getRepository()->find($id, $lockMode, $lockVersion);
    }

    /**
     * Tells Doctrine you want to (eventually) save the entity (no queries yet)
     * @param $entity
     */
    public function persist($entity) {
        $this->getRepository()->persist($entity);
    }

    /**
     * Tells Doctrine that you'd like to remove the given entity from the database (no queries yet)
     * @param $entity
     */
    public function remove($entity){
        $this->getRepository()->remove($entity);
    }

    public function refresh($entity) {
        $this->getRepository()->refresh($entity);
    }

    public function resetConnection()
    {
        $this->getRepository()->resetConnection();
    }

    /**
     * @return string|null
     */
    public function getEntityTableName() {
        return $this->getRepository()->getEntityTableName();
    }

    public function truncateTable()
    {
        $this->getRepository()->truncateTable();
    }

    /**
     * Actually executes the queries (i.e. the INSERT query)
     * @param null $entity
     */
    public function saveChanges($entity = null, $detach = false){
        $this->getRepository()->saveChanges($entity, $detach);
    }

    /**
     * Empty the table
     * @param bool $autocommit If false, you will need to flush to execute the request. Else, the table will be directly emptied
     * @return mixed
     */
    public function emptyTable($autocommit) {
        return $this->getRepository()->emptyTable($autocommit);
    }

    /**
     * Returns true if the table is not empty.
     * Warning: the entity must have its primary key named "id"
     * @return bool
     */
    public function any() {
        return $this->getRepository()->any();
    }

    public function clear() {
        $this->getRepository()->clear();
    }

    public function detach($entity) {
        $this->getRepository()->detach($entity);
    }

    public function disableSQLLogger()
    {
        $this->getRepository()->disableSQLLogger();
    }

    /**
     * @return \AppBundle\Entity\BaseEntity
     */
    public function getLatestSavedEntity() {
        return $this->getRepository()->getLatestSavedEntity();
    }

    public function beginTransaction()
    {
        $this->getRepository()->beginTransaction();
    }

    public function commit() {
        $this->getRepository()->commit();
    }

    public function rollback()
    {
        $this->getRepository()->rollback();
    }
}