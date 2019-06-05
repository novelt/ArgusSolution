<?php
/**
 * Created by PhpStorm.
 * User: nfargere
 * Date: 21/11/2016
 * Time: 16:17
 */

namespace AppBundle\Repository;


use AppBundle\Entity\BaseEntity;

interface RepositoryInterface
{
    public function find($id, $lockMode = null, $lockVersion = null);

    /**
     * Tells Doctrine you want to (eventually) save the entity (no queries yet)
     * @param $entity
     */
    public function persist($entity);

    /**
     * Tells Doctrine that you'd like to remove the given entity from the database (no queries yet)
     * @param $entity
     */
    public function remove($entity);

    /**
     * Refreshes the persistent state of an entity from the database,
     * overriding any local changes that have not yet been persisted.
     * @param $entity
     * @return mixed
     */
    public function refresh($entity);

    /**
     * Resets the entity manager.
     *
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     *
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @return mixed
     */
    public function resetConnection();

    /**
     * Returns the name in the database of the entity class attached to this repository instance
     * @return string|null
     */
    public function getEntityTableName();

    /**
     * Truncate the table.
     */
    public function truncateTable();

    /**
     * Actually executes the queries (i.e. the INSERT query)
     * @param null $entity
     */
    public function saveChanges($entity = null, $detach = false);

    /**
     * Empty the table
     * @param bool $autocommit If false, you will need to flush to execute the request. Else, the table will be directly emptied
     * @return mixed
     */
    public function emptyTable($autocommit);

    /**
     * @return BaseEntity
     * Returns the latest saved entity (row) in the table
     */
    public function getLatestSavedEntity();

    /**
     * @param $id
     * @return BaseEntity|null
     */
    public function findOneById($id);

    /**
     * Returns true if the table is not empty.
     * Warning: the entity must have its primary key named "id"
     * @return bool
     */
    public function any();

    /**
     * Finds all entities in the repository.
     *
     * @return array The entities.
     */
    public function findAll();

    /**
     * @return mixed
     */
    public function clear();

    /**
     * @param $entity
     * @return void
     */
    public function detach($entity);

    public function disableSQLLogger();

    public function beginTransaction();

    public function commit();

    public function rollback();
}