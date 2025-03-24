<?php
require_once 'BaseClass.php';
require_once 'database.class.php';

class FirstPersonText extends BaseClass {
    public function getText(): string {
        try {
            if (!$this->_dbh) {
                throw new PDOException("Connexion à la base de données non établie.");
            }

            $query = "SELECT t.text FROM text t
                      JOIN map m ON t.map_id = m.id
                      WHERE m.coordx = :x AND m.coordy = :y AND m.angle = :angle";

            $stmt = $this->_dbh->prepare($query);
            $stmt->bindValue(':x', $this->_currentX, PDO::PARAM_INT);
            $stmt->bindValue(':y', $this->_currentY, PDO::PARAM_INT);
            $stmt->bindValue(':angle', $this->_currentAngle, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result && isset($result['text']) ? $result['text'] : 'Aucun texte trouvé';
        } catch (PDOException $e) {
            error_log('Erreur SQL : ' . $e->getMessage()); // Enregistre l'erreur dans les logs
            return 'Erreur de base de données, veuillez réessayer plus tard.';
        }
    }
}
?>

