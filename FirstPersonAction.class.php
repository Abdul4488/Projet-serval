<?php
require_once 'BaseClass.php';

class FirstPersonAction extends BaseClass {
    private $_mapId = 1;

    public function __construct() {
        parent::__construct();
    }

    public function getMapId() {
        return $this->_mapId;
    }

    public function setMapId($id) {
        $this->_mapId = $id;
    }

    private function __currentMapId() {
        $y = $this->getCurrentY();
        $x = $this->getCurrentX();
        $angle = $this->getCurrentAngle();

        $sql = "SELECT * FROM map WHERE coordx = :x AND coordy = :y AND direction = :angle";
        $query = $this->getDbh()->prepare($sql);
        $query->bindParam(':y', $y, PDO::PARAM_INT);
        $query->bindParam(':x', $x, PDO::PARAM_INT);
        $query->bindParam(':angle', $angle, PDO::PARAM_INT);
        $query->execute();
        $newPos = $query->fetch(PDO::FETCH_OBJ);
        
        if ($newPos) {
            $this->setMapId($newPos->id);
        }
    }

    public function checkAction() {
        $this->__currentMapId();
        $dbh = $this->getDbh();

        $sql = "SELECT * FROM action JOIN map ON map.id = action.map_id 
                WHERE action.map_id = :map AND action.status = 0 AND map.status_action = 0";
        $query = $dbh->prepare($sql);
        $query->bindParam(':map', $this->_mapId, PDO::PARAM_INT);
        $query->execute();
        $obj = $query->fetch(PDO::FETCH_OBJ);

        $require = isset($obj->requis) ? $obj->requis : 0;

        if ($require == 1 && (!isset($_SESSION['items']) || $_SESSION['items'] == 0)) {
            return "disabled";
        }

        return isset($obj->id) ? "" : "disabled";
    }

    public function doAction() {
        $this->__currentMapId();
        $dbh = $this->getDbh();

        $sql = "UPDATE action SET status = 1 WHERE map_id = :map";
        $query = $dbh->prepare($sql);
        $query->bindParam(':map', $this->_mapId, PDO::PARAM_INT);
        $query->execute();

        $sql = "UPDATE map SET status_action = 1 WHERE id = :map";
        $query = $dbh->prepare($sql);
        $query->bindParam(':map', $this->_mapId, PDO::PARAM_INT);
        $query->execute();

        $sql = "UPDATE image SET status_action = 1 WHERE map_id = :map ORDER BY id ASC LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':map', $this->_mapId, PDO::PARAM_INT);
        $query->execute();

        $sql = "UPDATE text SET status_action = 1 WHERE map_id = :map ORDER BY id ASC LIMIT 1";
        $query = $dbh->prepare($sql);
        $query->bindParam(':map', $this->_mapId, PDO::PARAM_INT);
        $query->execute();

        $sql = "SELECT items.description FROM items 
                JOIN action ON action.item_id = items.id 
                WHERE action.map_id = :map";
        $query = $dbh->prepare($sql);
        $query->bindParam(':map', $this->_mapId, PDO::PARAM_INT);
        $query->execute();
        $obj = $query->fetch(PDO::FETCH_OBJ);

        if ($obj) {
            $_SESSION['items'] = isset($_SESSION['items']) && $_SESSION['items'] == 0 ? $obj->description : 1;
        }
    }
}
?>