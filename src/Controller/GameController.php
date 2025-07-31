<?php

namespace App\Controller;

use App\Model\Game;
use App\View;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

class GameController
{
	public static function handleAction(ServerRequestInterface $request, Game $game)
	{
		try {
			$body = (string) $request->getBody();
			$data = json_decode($body, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				return self::errorResponse('Invalid JSON in request');
			}

			$action = $data['action'] ?? '';

			$result = ['success' => true];

			switch ($action) {
				case 'select_class':
					$name = $data['name'] ?? 'Spieler';
					$playerClass = $data['class'] ?? 'Krieger';
					$game->createPlayer($name, $playerClass);
					break;

				case 'fight':
					$game->gameState = 'monster_selection';
					break;

				case 'select_enemy':
					$enemyType = $data['enemy'] ?? '';
					if (empty($enemyType)) {
						return self::errorResponse('Kein Gegner ausgewählt');
					}
					$game->spawnSelectedEnemy($enemyType);
					break;

				case 'attack':
					$game->playerAttack();
					break;

				case 'use_skill':
					$skillKey = $data['skillKey'] ?? '';
					if (empty($skillKey)) {
						return self::errorResponse('Keine Fähigkeit ausgewählt');
					}
					$success = $game->useSkill($skillKey);
					if (!$success) {
						$result['message'] = 'Konnte Fähigkeit nicht einsetzen';
					}
					break;

				case 'restore_energy':
					$game->player->restoreEnergy();
					break;

				case 'heal':
					$game->player->heal();
					break;

				case 'shop':
					$game->gameState = 'shop';
					break;

				case 'home':
					$game->gameState = 'home';
					break;

				case 'restart':
					$game->resetGame();
					break;

				case 'buy':
					$slot = $data['slot'] ?? '';
					$item = $data['item'] ?? '';
					if (empty($slot) || empty($item)) {
						return self::errorResponse('Ungültige Kaufparameter');
					}
					$success = $game->buyItem($slot, $item);
					if (!$success) {
						$result['message'] = 'Konnte Item nicht kaufen';
					}
					break;

				default:
					return self::errorResponse('Unbekannte Aktion: ' . $action);
			}

			$result['html'] = View\renderGame($game);

			return new Response(
					200,
					['Content-Type' => 'application/json; charset=UTF-8'],
					json_encode($result)
					);

		} catch (\Exception $e) {
			return self::errorResponse('Fehler: ' . $e->getMessage());
		}
	}

	private static function errorResponse($message, $code = 400)
	{
		return new Response(
				$code,
				['Content-Type' => 'application/json; charset=UTF-8'],
				json_encode([
						'success' => false,
						'message' => $message
				])
				);
	}
}