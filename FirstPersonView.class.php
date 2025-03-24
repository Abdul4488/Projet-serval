<?php

require_once 'BaseClass.php';
require_once 'FirstPersonAction.class.php';

class FirstPersonView extends BaseClass {
    const IMAGE_DIR = "images/";
    const MAP_DIR = "assets/";

    private ?int $mapId;

    public function __construct(PDO $dbh, int $coordx, int $coordy, int $angle) {
        parent::__construct($dbh, $coordx, $coordy, $angle);
        $this->mapId = $this->getMapId($coordx, $coordy);
    }

    public function getMapId(int $coorx, int $coordy): ?int {
        try {
            $query = "SELECT id FROM map WHERE coordx = ? AND coordy = ? AND direction = ?";
            $stmt = $this->dbh->prepare($query);
            $stmt->execute([$coorx, $coordy, $this->_currentAngle]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id'] ?? 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    public function setMapId(): void {
        try {
            $query = "SELECT id FROM map WHERE coordx = :x AND coordy = :y AND direction = :angle";
            $stmt = $this->dbh->prepare($query);
            $stmt->bindParam(':x', $this->_currentX, PDO::PARAM_INT);
            $stmt->bindParam(':y', $this->_currentY, PDO::PARAM_INT);
            $stmt->bindParam(':angle', $this->_currentAngle, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->mapId = $result['id'] ?? 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }

    public function getView(): string {

       
        $sql1 = "SELECT * FROM map WHERE coordx = :coordX 
                AND coordy = :coordy
                AND direction = :angle";
        $stmt1 = $this->dbh->prepare($sql1);
        $stmt1->execute([
            ':coordX' => $this->_currentX,
            ':coordy' => $this->_currentY,
            ':angle' => $this->_currentAngle
        ]);  
        $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        error_log("SQL1 Query Result: " . print_r($result1, true));
        
        if($result1) {
            $sql2 = "SELECT * FROM image WHERE map_id = $result1[id]";
            $stmt2 = $this->dbh->prepare($sql2);
            $stmt2->execute();
            $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

            error_log("result2 : ".print_r($result2, 1));

            return self::IMAGE_DIR . ($result2['path'] ?? "12-180.jpg");
        } else {
            return self::IMAGE_DIR . "12-180.jpg";
        }


     }

    public function getMapView(): string {
        if ($this->mapId === 0) {
            return self::MAP_DIR . "941723.jpg";
        }

        try {
            $query = "SELECT * FROM map WHERE id = :map_id";
            $stmt = $this->dbh->prepare($query);   
            $stmt->bindParam(':map_id', $this->mapId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);   
            return self::MAP_DIR . ($result['image'] ?? "941723.jpg");
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return self::MAP_DIR . "941723.jpg";
        }
    }
    
    public function getAnimCompass(): string {
       return match ($this->_currentAngle) {
           0 => "east",
           90 => "north",
           180 => "west",
           270 => "south",
           default => "north",
       };
    }

    public function getCurrentAngle(): int {
        return $this->_currentAngle;
    }

    public function isActionPossible(): bool {
        // verifier s'il y a un objet a cette position et s'il est disponible
        $query = "SELECT * FROM action WHERE map_id = :map_id 
                AND status = 1";
        $stmt = $this->dbh->prepare($query);
        $stmt->bindParam(':map_id', $this->mapId, PDO::PARAM_INT);
        //$stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$item) {
            return false;
    }
    // verifier si l'objet requis est dans l'inventaire
    if($item['requis'] !== 0) {
        if(!isset($_SESSION['inventory']) || !in_array($item['requis'], $_SESSION['inventory'])) {
            return false;
        }
    }
    return true;
    }
    public static function restoreState(PDO $dbh, array $data): self {
        return new self($dbh, (int)$data['x'], (int)$data['y'], (int)$data['angle']);
    }

     public function getAvailableMoves() {
        // Implement the logic to get available moves
        return [
            'left' => 1,
            'forward' => 1,
            'right' => 1,
            'back' => 1
        ];
    }
}
?>
