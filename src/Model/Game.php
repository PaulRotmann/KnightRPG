<?php

namespace App\Model;

use App\Model\Player;
use App\Model\Enemy;

class Game {
	public $player;
	public $currentEnemy;
	public $gameState; // 'home', 'combat', 'shop', 'gameover', 'monster_selection'
	public $combatLog;

	public function __construct() {
		$this->player = null; // Start with no player
		$this->currentEnemy = null;
		$this->gameState = 'class_selection'; // Start with class selection
		$this->combatLog = [];
	}

	// New method to create a player with selected class
	public function createPlayer($name, $playerClass) {
		$this->player = new Player($name, $playerClass);
		$this->gameState = 'home';
		return true;
	}

	public function getEnemyTypes() {
		return [
				'Wolf' => ['hp' => [25, 35], 'damage' => [4, 8], 'exp' => [10, 20], 'gold' => [3, 10]],
				'Goblin' => ['hp' => [35, 50], 'damage' => [5, 10], 'exp' => [15, 25], 'gold' => [5, 15]],
				'Skelett' => ['hp' => [50, 100], 'damage' => [8, 15], 'exp' => [20, 35], 'gold' => [8, 20]],
				'Zombie' => ['hp' => [100, 120], 'damage' => [10, 18], 'exp' => [18, 30], 'gold' => [10, 25]],
				'Bandit' => ['hp' => [120, 150], 'damage' => [10, 20], 'exp' => [25, 45], 'gold' => [10, 30]],
				'Orc' => ['hp' => [150, 180], 'damage' => [10, 18], 'exp' => [25, 40], 'gold' => [10, 25]],
				'Hexe' => ['hp' => [180, 200], 'damage' => [12, 22], 'exp' => [35, 55], 'gold' => [12, 35]],
				'Golem' => ['hp' => [200, 230], 'damage' => [15, 28], 'exp' => [40, 60], 'gold' => [18, 45]],
				'Vampir' => ['hp' => [230, 250], 'damage' => [18, 30], 'exp' => [45, 70], 'gold' => [20, 50]],
				'Troll' => ['hp' => [250, 300], 'damage' => [20, 35], 'exp' => [60, 90], 'gold' => [30, 70]],
				'Riese' => ['hp' => [300, 500], 'damage' => [30, 50], 'exp' => [80, 120], 'gold' => [50, 100]],
				'Drache' => ['hp' => [500, 1000], 'damage' => [50, 80], 'exp' => [100, 150], 'gold' => [70, 120]]
		];
	}

	public function spawnSelectedEnemy($enemyType) {
		$enemyTypes = $this->getEnemyTypes();
		if (!isset($enemyTypes[$enemyType])) {
			return false;
		}

		$stats = $enemyTypes[$enemyType];
		$hp = rand($stats['hp'][0], $stats['hp'][1]);
		$damage = rand($stats['damage'][0], $stats['damage'][1]);
		$exp = rand($stats['exp'][0], $stats['exp'][1]);
		$gold = rand($stats['gold'][0], $stats['gold'][1]);

		$this->currentEnemy = new Enemy($enemyType, $hp, $damage, $exp, $gold);
		$this->gameState = 'combat';
		$this->combatLog = [];
		$this->combatLog[] = "Du trittst gegen einen {$enemyType} an! (HP: {$hp}, Schaden: {$damage})";
		return true;
	}

	public function playerAttack() {
		if ($this->currentEnemy && $this->currentEnemy->isAlive()) {
			$damage = $this->player->attack($this->currentEnemy);
			$this->combatLog[] = "Du greifst den {$this->currentEnemy->name} an und machst {$damage} Schaden!";

			if (!$this->currentEnemy->isAlive()) {
				$this->endCombat();
			} else {
				$this->enemyAttack();

				// Regeneriere etwas Energie nach dem Zug
				$regenAmount = $this->player->regenerateEnergy();
				$this->combatLog[] = "Du regenerierst " . $regenAmount . " Energiepunkte.";
			}
		}
	}

	public function useSkill($skillKey) {
		if (!$this->currentEnemy || !$this->currentEnemy->isAlive()) {
			return;
		}

		$result = $this->player->useSkill($skillKey, $this->currentEnemy);

		if (!$result['success']) {
			$this->combatLog[] = $result['message'];
			return;
		}

		$this->combatLog[] = "Du setzt " . $result['skillName'] . " ein und fügst " . $result['damage'] . " Schaden zu!";

		if (!$this->currentEnemy->isAlive()) {
			$this->endCombat();
		} else {
			// Gegner greift zurück an
			$this->enemyAttack();

			// Regeneriere etwas Energie nach dem Zug
			$regenAmount = $this->player->regenerateEnergy();
			$this->combatLog[] = "Du regenerierst " . $regenAmount . " Energiepunkte.";
		}
	}

	public function enemyAttack() {
		if ($this->currentEnemy && $this->currentEnemy->isAlive()) {
			$damage = $this->currentEnemy->attack($this->player);
			$this->combatLog[] = "Der {$this->currentEnemy->name} greift dich an und macht {$damage} Schaden!";

			if ($this->player->hp <= 0) {
				$this->combatLog[] = "Du bist gestorben!";
				$this->gameState = 'gameover';
				$this->currentEnemy = null;
			}
		}
	}

	public function endCombat() {
		$exp = $this->currentEnemy->expReward;
		$gold = $this->currentEnemy->goldReward;

		$this->combatLog[] = "Der {$this->currentEnemy->name} ist besiegt!";
		$this->combatLog[] = "Du erhältst {$exp} EXP und {$gold} Gold!";

		$oldLevel = $this->player->level;
		$this->player->gainExp($exp);
		$this->player->gold += $gold;

		if ($this->player->level > $oldLevel) {
			$levelUpResults = $this->player->levelUp();
			$this->combatLog[] = "Level Up! Du bist jetzt Level {$this->player->level}!";
			$this->combatLog[] = "HP +{$levelUpResults['hpIncrease']} | Energie +{$levelUpResults['energyIncrease']}";

			// Prüfe, ob neue Fertigkeiten freigeschaltet wurden
			foreach ($this->player->skills as $key => $skill) {
				if ($this->player->level == $skill['unlockLevel']) {
					$this->combatLog[] = "Neue Fertigkeit freigeschaltet: {$skill['name']}!";
				}
			}
		}

		$this->gameState = 'home';
		$this->currentEnemy = null;
	}

	public function getShopItems() {
		$baseItems = [
				'helmet' => [
						'Eisenhelm' => ['price' => 50, 'defense' => 5],
						'Stahlhelm' => ['price' => 150, 'defense' => 12],
						'Ritterhelm' => ['price' => 300, 'defense' => 20]
				],
				'armor' => [
						'Lederrüstung' => ['price' => 100, 'defense' => 10],
						'Kettenhemd' => ['price' => 250, 'defense' => 20],
						'Plattenrüstung' => ['price' => 500, 'defense' => 35]
				],
				'gloves' => [
						'Lederhandschuhe' => ['price' => 30, 'defense' => 3],
						'Eisenhandschuhe' => ['price' => 80, 'defense' => 7],
						'Stahlhandschuhe' => ['price' => 150, 'defense' => 12]
				],
				'boots' => [
						'Lederstiefel' => ['price' => 40, 'defense' => 4],
						'Eisenstiefel' => ['price' => 90, 'defense' => 8],
						'Stahlstiefel' => ['price' => 180, 'defense' => 15]
				],
				'ring' => [
						'Kraftring' => ['price' => 200, 'damage' => 8],
						'Machtring' => ['price' => 400, 'damage' => 15],
						'Drachenring' => ['price' => 800, 'damage' => 25]
				],
				'amulet' => [
						'Schutzamulett' => ['price' => 150, 'defense' => 8],
						'Kraftamulett' => ['price' => 300, 'damage' => 12],
						'Legendäres Amulett' => ['price' => 600, 'damage' => 20, 'defense' => 10]
				]
		];
		if ($this->player) {
			switch($this->player->playerClass) {
				case "Magier":
					$baseItems['weapon'] = [
					'Holzstab' => ['price' => 75, 'damage' => 8],
					'Zauberstab' => ['price' => 200, 'damage' => 18],
					'Magierstab' => ['price' => 450, 'damage' => 30],
					'Meisterstab' => ['price' => 1000, 'damage' => 50]
					];
					break;
				case "Schütze":
					$baseItems['weapon'] = [
					'Kurzbogen' => ['price' => 75, 'damage' => 8],
					'Langbogen' => ['price' => 200, 'damage' => 18],
					'Kompositbogen' => ['price' => 450, 'damage' => 30],
					'Drachenjägerbogen' => ['price' => 1000, 'damage' => 50]
					];
					break;
				case "Krieger":
				default:
					$baseItems['weapon'] = [
					'Rostiges Schwert' => ['price' => 75, 'damage' => 8],
					'Eisenschwert' => ['price' => 200, 'damage' => 18],
					'Stahlschwert' => ['price' => 450, 'damage' => 30],
					'Verzaubertes Schwert' => ['price' => 1000, 'damage' => 50]
					];
					break;
			}
		}

		return $baseItems;
	}

	public function buyItem($slot, $itemName) {
		$shopItems = $this->getShopItems();
		if (isset($shopItems[$slot][$itemName])) {
			$item = $shopItems[$slot][$itemName];
			if ($this->player->gold >= $item['price']) {
				$this->player->gold -= $item['price'];
				$this->player->equipItem($item, $slot);
				return true;
			}
		}
		return false;
	}

	public function resetGame() {
		$this->player = null;
		$this->currentEnemy = null;
		$this->gameState = 'class_selection';
		$this->combatLog = [];
	}
}