<?php 

require_once 'BaseClass.php';
require_once 'index.php';
class BaseClass {
    protected int $_currentX;
    protected int $_currentY;
    protected int $_currentAngle;
    protected PDO $dbh;

    public function __construct(PDO $dbh, int $currentX, int $currentY, int $angle) {
        $this->dbh = $dbh;
        $this->_currentX = $currentX;
        $this->_currentY = $currentY;
        $this->_currentAngle = $angle;
    }

    public function getX(): int { return $this->_currentX; }
    public function getY(): int { return $this->_currentY; }
    public function getAngle(): int { return $this->_currentAngle; }

    public function setX(int $newX): void { $this->_currentX = $newX; }
    public function setY(int $newY): void { $this->_currentY = $newY; }
    public function setAngle(int $newAngle): void { $this->_currentAngle = $newAngle; }

    private function _checkMove(int $x, int $y, ?int $angle = null): bool {
        if ($angle === null) {
            $angle = $this->_currentAngle;
        }
        try {
            $query = "SELECT COUNT(*) FROM map WHERE coordx = :x AND coordy = :y AND direction = :angle";
            $stmt = $this->dbh->prepare($query);
            $stmt->bindParam(':x', $x, PDO::PARAM_INT);
            $stmt->bindParam(':y', $y, PDO::PARAM_INT);
            $stmt->bindParam(':angle', $angle, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function checkForward(): bool {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newX++; break;
            case 90:  $newY++; break;
            case 180: $newX--; break;
            case 270: $newY--; break;
        }
        return $this->_checkMove($newX, $newY, $this->_currentAngle);
    }

    public function checkBack(): bool {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newX--; break;
            case 90:  $newY--; break;
            case 180: $newX++; break;
            case 270: $newY++; break;
        }
        return $this->_checkMove($newX, $newY, $this->_currentAngle);
    }

    public function checkRight(): bool {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newY++; break;
            case 90:  $newX--; break;
            case 180: $newY--; break;
            case 270: $newX++; break;
        }
        return $this->_checkMove($newX, $newY, $this->_currentAngle);
    }

    public function checkLeft(): bool {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newY--; break;
            case 90:  $newX++; break;
            case 180: $newY++; break;
            case 270: $newX--; break;
        }
        return $this->_checkMove($newX, $newY, $this->_currentAngle);
    }

    public function checkTurnRight(): bool {
        $newAngle = ($this->_currentAngle - 90 + 360) % 360;
        return $this->_checkMove($this->_currentX, $this->_currentY, $newAngle);
    }

    public function checkTurnLeft(): bool {
        $newAngle = ($this->_currentAngle + 90) % 360;
        return $this->_checkMove($this->_currentX, $this->_currentY, $newAngle);
    }

    private function _move(int $x, int $y, int $angle): void {
        error_log("_move() a fonctionée! Yeni X: $x, Yeni Y: $y, Açı: $angle");
        $this->_currentX = $x;
        $this->_currentY = $y;
        $this->_currentAngle = $angle;
        error_log("_move() actuelisée! currentX: {$this->_currentX}, currentY: {$this->_currentY}");
    }

    public function goForward(): bool {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        $newAngle = $this->_currentAngle;
        switch ($this->_currentAngle) {
            case 0:   $newX++; break;
            case 90:  $newY++; break;
            case 180: $newX--; break;
            case 270: $newY--; break;
        }

        if ($this->_checkMove($newX, $newY, $newAngle)) {
            $this->_currentX = $newX;
            $this->_currentY = $newY;
            $this->_currentAngle = $newAngle;
              
            $this->setMapId();
            return true;
        }
        return false;
    }

    public function goBack(): void {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newX--; break;
            case 90:  $newY--; break;
            case 180: $newX++; break;
            case 270: $newY++; break;
        }

        if ($this->_checkMove($newX, $newY, $this->_currentAngle)) {
            $this->_move($newX, $newY, $this->_currentAngle);
        }
    }

    public function goLeft(): void {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newY++; break;
            case 90:  $newX--; break;
            case 180: $newY--; break;
            case 270: $newX++; break;
        }
        error_log("goRight()  -> X: $newX, Y: $newY, Açı: $this->_currentAngle");
        if ($this->_checkMove($newX, $newY, $this->_currentAngle)) {
            $this->_move($newX, $newY, $this->_currentAngle);
        }
    }

    public function goRight(): void {
        $newX = $this->_currentX;
        $newY = $this->_currentY;
        switch ($this->_currentAngle) {
            case 0:   $newY--; break;
            case 90:  $newX++; break;
            case 180: $newY++; break;
            case 270: $newX--; break;
        }

        if ($this->_checkMove($newX, $newY, $this->_currentAngle)) {
            $this->_move($newX, $newY, $this->_currentAngle);
        }
    }

    public function turnRight(): void {
        $newAngle = ($this->_currentAngle - 90 + 360) % 360;
        if ($this->_checkMove($this->_currentX, $this->_currentY, $newAngle)) {
            $this->_move($this->_currentX, $this->_currentY, $newAngle);
        }
    }

    public function turnLeft(): void {
        $newAngle = ($this->_currentAngle + 90) % 360;
        error_log("currentAngle : ".$this->_currentAngle);
        error_log("newAngle : ".$newAngle);
        if ($this->_checkMove($this->_currentX, $this->_currentY, $newAngle)) {
            $this->_move($this->_currentX, $this->_currentY, $newAngle);
        }
    }
    
      
}


?>

