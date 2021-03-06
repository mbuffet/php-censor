<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCensor\Store;

use b8\Database;
use PHPCensor\Model\Project;
use PHPCensor\Store\Base\ProjectStoreBase;

/**
* Project Store
* @author       Dan Cryer <dan@block8.co.uk>
* @package      PHPCI
* @subpackage   Core
*/
class ProjectStore extends ProjectStoreBase
{
    /**
     * Returns a list of all branch names PHPCI has run builds against.
     * @param $projectId
     * @return array
     */
    public function getKnownBranches($projectId)
    {
        $query = 'SELECT DISTINCT {{branch}} from {{build}} WHERE {{project_id}} = :pid';
        $stmt = Database::getConnection('read')->prepareCommon($query);
        $stmt->bindValue(':pid', $projectId);

        if ($stmt->execute()) {
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $map = function ($item) {
                return $item['branch'];
            };
            $rtn = array_map($map, $res);

            return $rtn;
        } else {
            return [];
        }
    }

    /**
     * Get a list of all projects, ordered by their title.
     * 
     * @param boolean $archived
     * 
     * @return array
     */
    public function getAll($archived = false)
    {
        $archived = (integer)$archived;

        $query = 'SELECT * FROM {{project}} WHERE {{archived}} = :archived ORDER BY {{title}} ASC';
        $stmt  = Database::getConnection('read')->prepareCommon($query);

        $stmt->bindValue(':archived', $archived);

        if ($stmt->execute()) {
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $map = function ($item) {
                return new Project($item);
            };
            $rtn = array_map($map, $res);

            $count = count($rtn);


            return ['items' => $rtn, 'count' => $count];
        } else {
            return ['items' => [], 'count' => 0];
        }
    }

    /**
     * Get multiple Project by GroupId.
     * 
     * @param integer $value
     * @param boolean $archived
     * @param integer $limit
     * @param string  $useConnection
     * 
     * @return array
     * 
     * @throws \Exception
     */
    public function getByGroupId($value, $archived = false, $limit = 1000, $useConnection = 'read')
    {
        if (is_null($value)) {
            throw new \Exception('Value passed to ' . __FUNCTION__ . ' cannot be null.');
        }
        $archived = (integer)$archived;

        $query = 'SELECT * FROM {{project}} WHERE {{group_id}} = :group_id AND {{archived}} = :archived ORDER BY {{title}} LIMIT :limit';
        $stmt  = Database::getConnection($useConnection)->prepareCommon($query);

        $stmt->bindValue(':group_id', $value);
        $stmt->bindValue(':archived', $archived);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);

        if ($stmt->execute()) {
            $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $map = function ($item) {
                return new Project($item);
            };
            $rtn = array_map($map, $res);

            $count = count($rtn);

            return ['items' => $rtn, 'count' => $count];
        } else {
            return ['items' => [], 'count' => 0];
        }
    }
}
