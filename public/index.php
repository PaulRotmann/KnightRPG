<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/View/Render.php';

use FrameworkX\App;
use App\Model\Game;
use App\View;
use App\Controller\GameController;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

// Session Management
session_start();

if (!isset($_SESSION['game'])) {
	$_SESSION['game'] = new Game();
}

$game = $_SESSION['game'];

// Framework X App
$app = new App();

// Routes
$app->get('/', function () use ($game) {
	return new Response(200, ['Content-Type' => 'text/html; charset=UTF-8'], View\renderGame($game));
});

	$app->post('/action', function (ServerRequestInterface $request) use ($game) {
		return GameController::handleAction($request, $game);
	});

		// Start the app
		$app->run();
