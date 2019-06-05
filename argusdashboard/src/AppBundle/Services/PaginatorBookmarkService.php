<?php
/**
 * Paginator Bookmark Service
 *
 * @author François Cardinaux
 */

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class PaginatorBookmarkService
 * @package AppBundle\Services
 */
class PaginatorBookmarkService
{
    const SESSION_KEY = 'PaginatorBookmarkService';
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Remove all bookmarks
     */
    public function initBookmarks()
    {
        $this->session->set(self::SESSION_KEY, array());
    }

    /**
     * Get the saved page for the specified list
     *
     * @param $listKey
     * @return int
     */
    public function getPage($listKey)
    {
        $bookmarks = $this->session->get(self::SESSION_KEY);

        return  isset($bookmarks[$listKey]) ? $bookmarks[$listKey] : 1;
    }

    /**
     * Save the page of the specified list
     *
     * @param $listKey
     * @param $page
     */
    public function setPage($listKey, $page)
    {
        $bookmarks = $this->session->get(self::SESSION_KEY);

        $bookmarks[$listKey] = $page;

        $this->session->set(self::SESSION_KEY, $bookmarks);
    }

    /**
     * Set the page of the specified list to one
     *
     * @param $listKey
     */
    public function initPage($listKey)
    {
        $this->setPage($listKey, 1);
    }

}