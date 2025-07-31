<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/View/Render.php';

use FrameworkX\App;
use App\Model\Game;
use App\View;
use App\Controller\GameController;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

// Start session with improved security
session_start([
    'cookie_httponly' => true,    // Prevent JavaScript access to session cookie
    'cookie_secure' => !empty($_SERVER['HTTPS']), // Use secure cookies if on HTTPS
    'use_strict_mode' => true     // Use strict session mode
]);

// Helper function to get or create game instance
function getGameInstance() {
    if (!isset($_SESSION['game']) || !($_SESSION['game'] instanceof Game)) {
        $_SESSION['game'] = new Game();
    }
    return $_SESSION['game'];
}

// Regenerate session ID occasionally to prevent fixation attacks
if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

$app = new App();

$app->get('/', function(ServerRequestInterface $request) {
    try {
        $game = getGameInstance();
        return new Response(
            200,
            ['Content-Type' => 'text/html; charset=UTF-8'],
            View\renderGame($game)
        );
    } catch (\Exception $e) {
        return new Response(
            500,
            ['Content-Type' => 'text/html; charset=UTF-8'],
            'An error occurred: ' . $e->getMessage()
        );
    }
});

$app->post('/action', function(ServerRequestInterface $request) {
    try {
        $game = getGameInstance();
        $response = GameController::handleAction($request, $game);
        $_SESSION['game'] = $game; // Save game state
        return $response;
    } catch (\Exception $e) {
        return new Response(
            500,
            ['Content-Type' => 'application/json; charset=UTF-8'],
            json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()])
        );
    }
});

$app->run();