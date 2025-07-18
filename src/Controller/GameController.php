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
		$data = json_decode($request->getBody(), true);
		$action = $data['action'] ?? '';

		switch ($action) {
			case 'select_class':
				$name = $data['name'] ?? 'Ritter';
				$playerClass = $data['class'] ?? 'Ritter';
				$game->createPlayer($name, $playerClass);
				break;
			case 'fight':
				$game->gameState = 'monster_selection';
				break;
			case 'select_enemy':
				$enemyType = $data['enemy'] ?? '';
				$game->spawnSelectedEnemy($enemyType);
				break;
			case 'attack':
				$game->playerAttack();
				break;
			case 'use_skill':
				$skillKey = $data['skillKey'] ?? '';
				$game->useSkill($skillKey);
				break;
			case 'restore_energy':
				$restored = $game->player->restoreEnergy();
				break;
			case 'heal':
				$healed = $game->player->heal();
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
				$game->buyItem($slot, $item);
				break;
		}

		$_SESSION['game'] = $game;

		return new Response(200, ['Content-Type' => 'application/json; charset=UTF-8'], json_encode([
				'success' => true,
				'html' => View\renderGame($game)
		]));
	}
}
