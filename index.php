<?php

session_start();

error_log("POST : ".print_r($_POST, 1));

$currentX = isset($_SESSION['currentX']) ? (int) $_SESSION['currentX'] : 0;
$currentY = isset($_SESSION['currentY']) ? (int) $_SESSION['currentY'] : 1;
$currentAngle = isset($_SESSION['currentAngle']) ? (int) $_SESSION['currentAngle'] : 0;


spl_autoload_register(function($className) {
    $file = __DIR__ . '/' . $className . '.class.php';
    if (file_exists($file)) {
        include $file;
    } else {
        die("Class file not found: $className");
    }
});

    $dbh = new PDO("mysql:host=localhost;dbname=fpview", "root", "root");
    



if(!isset($_SESSION['player_data'])) {
    $coordx = 0;
    $coordy = 1;
    $angle = 0;
    $_SESSION['player_data'] = ['x' => $coordx, 'y' => $coordy, 'angle' => $angle];
}



$player = FirstPersonView::restoreState($dbh, $_SESSION['player_data']);
$moves = $player->getAvailableMoves();


error_log("Page a chargée ! Actuel X: " . $player->getX() . ", Actuel Y: " . $player->getY());

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['move'])) {
    $direction = $_POST['move'];
    $imagePath = $player->getView();



    // $mapPath = "images/default.jpg";
    switch ($direction) {
        case 'forward':
            $player->goForward();
            break;
        case 'backward':
            $player->goBack();
            break;
        case 'left':
            $player->goLeft();
            break;
        case 'right':
            $player->goRight();
            break;
        case 'turn_left':
            $player->turnLeft();
            break;
        case 'turn_right':
            $player->turnRight();
            break;
    }
    
    $_SESSION['player_data'] = ['x' => $player->getX(), 'y' => $player->getY(), 'angle' => $player->getAngle()];

    
    $imagePath = $player->getView();
    error_log("" . $imagePath);
    $mapPath = $player->getMapView();
    
  
    $coordx = $player->getX();
    $coordy = $player->getY();
    $angle = $player->getAngle();
} else {
    $imagePath = $player->getView();
    $mapPath = $player->getMapView();

    error_log($imagePath);
}

error_log("getView() : " . $player->getView());






$dbh = new Database();


$posX = $player->getX();
$posY = $player->getY();
$direction = $player->getAngle();

try {
    $query = "SELECT * FROM map WHERE coordx = ? AND coordy = ? AND direction = ?";
    $stmt = $dbh->prepare($query);
    $stmt->execute([$posX, $posY, $direction]);
    $mapData = $stmt->fetch(PDO::FETCH_ASSOC);


error_log(" " . print_r($mapData, 1));


    $map_id = $mapData ? $mapData['id'] : 0;
    $message = "Il n'y a pas de chemin...";

    if ($map_id !== 0) {
        $query = "SELECT * FROM text WHERE map_id = ?";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$map_id]);
        $textData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($textData) {
            $message = $textData['text'];
        }
    }
} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}

$actionPossible = $player->isActionPossible();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $move = $_POST["move"] ?? null;
    if ($move === "action") {
        $currentX = $_SESSION['currentX'] ?? 0;
        $currentY = $_SESSION['currentY'] ?? 0;
        $currentAngle = $_SESSION['currentAngle'] ?? 0;

error_log("🔍 Button Action a ete appuyee! X: $currentX, Y: $currentY, Angle: $currentAngle");
error_log("🔍 Map ID: $map_id");


        $FirstPersonAction = new FirstPersonAction($dbh, $currentX, $currentY, $currentAngle, $map_id);

        $imagePath = './images/' . $firstPersonAction->getView();

        $actionPossible = true;        

error_log($imagePath);

        $FirstPersonAction->doAction();

        try {
            $query = "SELECT * FROM map WHERE coordx = ? AND coordy = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$currentX, $currentY]); 
            $mapData = $stmt->fetch(PDO::FETCH_ASSOC);
            $map_id = $mapData ? $mapData['id'] : 0;
            }
         catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    }
}

// Si c'est dans un tableau
$status['status_action'] = 1;

// Ou si c'est une variable simple
$status_action = 1;

$actionPossible = ""; // Vous pouvez définir des conditions ici

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Action à exécuter lors du clic sur le bouton
  
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projet serval</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>



    <div id="first-person-view">
        <img src="<?= htmlspecialchars($imagePath) ?>?v=<?= time(); ?>" alt="jeu" id="game-view">
    </div>
    
    <div id="bottom-section">
        <div id="controls">
            <form method="post">
                <table>
                   
                    <tr>
                        <td><button type="submit" name="move" value="left" <?=($moves['left'] == 1) ? '' : 'disabled' ?> >⬅️ </button></td>
                        <td><button type="submit" name="move" value="forward" <?=($moves['forward'] == 1) ? '' : 'disabled' ?>>⬆️</button></td>
                        <td><button type="submit" name="move" value="right" <?=($moves['right'] == 1) ? '' : 'disabled' ?>>➡️ </button></td>
                    </tr>
                    
                    <tr>
                        <td><button class="turn-icon" type="submit" name="move" value="turn_left">↰ </button></td>
                        <td><button type="submit" name="move" value="backward" <?=($moves['back'] == 1) ? '' : 'disabled' ?>>⬇️ </button></td>
                        <td><button class="turn-icon" type="submit" name="move" value="turn_right">↱ </button></td>
                        
                    </tr>
                    <div id="action">
                    <button class="hand-icon" type="submit" <?php echo $actionPossible; ?>>✋</button>
                    </div>
                </table>
            </form>

<?php
// Pure function to generate compass HTML
function renderCompass(string $animCompassClass): string {
    return '<div id="compass" class="' . htmlspecialchars($animCompassClass, ENT_QUOTES, 'UTF-8') . '">
                <img src="./assets/compass.png" alt="Boussole">
            </div>';
}

// Function to get the CSS class from the player object
function getAnimCompass(object $player): string {
    return method_exists($player, 'getAnimCompass') ? $player->getAnimCompass() : 'rotate-0';
}

// Assuming $player is a valid object
if (isset($player) && is_object($player)) {
    $animCompassClass = getAnimCompass($player);
    echo renderCompass($animCompassClass);
} else {
    echo renderCompass('rotate-0'); // Default class if $player is not set or not an object
}
?>

        </div>
        <!--Maps-->
        <div id="map-container">
            <img src="<?= $mapPath ?>" alt="map" id="map-view">

            <div id="map-text">
                <p><?= $message ?></p>
            </div>
        </div>

    </div>

   

</body>
</html>
