<?php

namespace App\Model;

use App\Model\StatusEffect;

class Player {
	public $name;
	public $level;
	public $hp;
	public $maxHp;
	public $damage;
	public $defense;
	public $exp;
	public $expToNext;
	public $gold;
	public $equipment;
	public $energy;
	public $maxEnergy;
	public $skills;
	public $playerClass;
	public $dodgeChance;
	public $blockChance;

	public $statusEffects = [];

	public function __construct($name = "Ritter", $playerClass = "Krieger") {
		$this->name = $name;
		$this->level = 1;
		$this->playerClass = $playerClass;

		// Base stats differ by class
		switch($playerClass) {
			case "Magier":
				$this->hp = 80;
				$this->maxHp = 80;
				$this->damage = 20;
				$this->defense = 3;
				$this->energy = 150;
				$this->maxEnergy = 150;
				$this->dodgeChance = 5;
				$this->blockChance = 5;
				break;
			case "Schütze":
				$this->hp = 100;
				$this->maxHp = 100;
				$this->damage = 18;
				$this->defense = 4;
				$this->energy = 120;
				$this->maxEnergy = 120;
				$this->dodgeChance = 10;
				$this->blockChance = 3;
				break;
			case "Krieger":
			default:
				$this->hp = 120;
				$this->maxHp = 120;
				$this->damage = 15;
				$this->defense = 5;
				$this->energy = 100;
				$this->maxEnergy = 100;
				$this->dodgeChance = 3;
				$this->blockChance = 10;
				break;
		}

		$this->exp = 0;
		$this->expToNext = 100;
		$this->gold = 50;
		$this->equipment = [
				'helmet' => null,
				'armor' => null,
				'gloves' => null,
				'boots' => null,
				'weapon' => null,
				'ring' => null,
				'amulet' => null
		];

		// Set class-specific skills
		$this->initSkills();
	}

	public function initSkills() {
		// Base skills structure
		$this->skills = [];

		switch($this->playerClass) {
			case "Magier":
				$this->skills = [
				'feuerball' => [
				'name' => 'Feuerball',
				'unlockLevel' => 3,
				'energyCost' => 35,
				'damageMultiplier' => 1.5,
				'description' => 'Ein mächtiger Feuerball, der großen Schaden verursacht.'
						],
						'eisstrahl' => [
						'name' => 'Eisstrahl',
						'unlockLevel' => 8,
						'energyCost' => 50,
						'damageMultiplier' => 2.0,
						'description' => 'Ein Strahl aus Eis, der dem Gegner schadet.'
								],
								'kettenblitz' => [
								'name' => 'Kettenblitz',
								'unlockLevel' => 13,
								'energyCost' => 70,
								'damageMultiplier' => 2.5,
								'description' => 'Ein Blitz, der gewaltigen Schaden anrichtet.'
										]
										];
				break;
			case "Schütze":
				$this->skills = [
				'gezielter_schuss' => [
				'name' => 'Gezielter Schuss',
				'unlockLevel' => 3,
				'energyCost' => 25,
				'damageMultiplier' => 1.4,
				'description' => 'Ein präziser Schuss an eine verwundbare Stelle.'
						],
						'mehrfachschuss' => [
						'name' => 'Mehrfachschuss',
						'unlockLevel' => 8,
						'energyCost' => 40,
						'damageMultiplier' => 1.8,
						'description' => 'Mehrere Pfeile werden gleichzeitig abgefeuert.'
								],
								'explosivpfeil' => [
								'name' => 'Explosivpfeil',
								'unlockLevel' => 13,
								'energyCost' => 55,
								'damageMultiplier' => 2.2,
								'description' => 'Ein Pfeil, der bei Aufprall explodiert.'
										]
										];
				break;
			case "Krieger":
			default:
				$this->skills = [
				'zerschmettern' => [
				'name' => 'Zerschmettern',
				'unlockLevel' => 3,
				'energyCost' => 30,
				'damageMultiplier' => 1.3,
				'description' => 'Ein mächtiger Schlag, der erhöhten Schaden verursacht.'
						],
						'sprungangriff' => [
						'name' => 'Sprungangriff',
						'unlockLevel' => 8,
						'energyCost' => 45,
						'damageMultiplier' => 1.7,
						'description' => 'Ein Sprung auf den Gegner mit verheerender Wucht.'
								],
								'wirbelwind' => [
								'name' => 'Wirbelwind',
								'unlockLevel' => 13,
								'energyCost' => 60,
								'damageMultiplier' => 2.0,
								'description' => 'Eine rotierende Attacke, die zweifachen Schaden verursacht.'
										]
										];
				break;
		}
	}

	public function getEquipmentDodgeBonus() {
		$bonus = 0;
		foreach ($this->equipment as $item) {
			if ($item && isset($item['dodge'])) $bonus += $item['dodge'];
		}
		return $bonus;
	}
	public function getEquipmentBlockBonus() {
		$bonus = 0;
		foreach ($this->equipment as $item) {
			if ($item && isset($item['block'])) $bonus += $item['block'];
		}
		return $bonus;
	}

	public function attack($enemy) {
		if (!$this->canAct()) {
			// Spieler ist bewegungsunfähig
			return 0;
		}
		$baseDamage = rand($this->damage - 5, $this->damage + 5);
		$equipmentBonus = $this->getEquipmentDamageBonus();
		$totalDamage = $baseDamage + $equipmentBonus;

		// Krieger/Schütze: Chance auf Bluten bei jedem normalen Angriff
		if (
				($this->playerClass === "Schütze" || $this->playerClass === "Krieger")
				&& rand(1,100) <= 20
				) {
					$staerke = round(($this->damage + $this->getEquipmentDamageBonus()) * 0.3);
					$enemy->addStatusEffect(new \App\Model\StatusEffect('bleed', 3, $staerke));
					// combatLog musst du hier vom Aufrufer aus verarbeiten!
				}

				$actualDamage = $enemy->takeDamage($totalDamage);
				return $actualDamage;
	}

	public function useSkill($skillKey, $enemy) {
		if (!$this->canAct()) {
			// Spieler ist bewegungsunfähig
			return ['success' => false, 'message' => 'Du kannst jetzt nicht angreifen!'];
		}

		if (!isset($this->skills[$skillKey])) {
			return ['success' => false, 'message' => 'Ungültige Fertigkeit!'];
		}

		$skill = $this->skills[$skillKey];

		if ($this->energy < $skill['energyCost']) {
			return ['success' => false, 'message' => 'Nicht genug Energie!'];
		}

		$baseDamage = rand($this->damage - 5, $this->damage + 5);
		$equipmentBonus = $this->getEquipmentDamageBonus();

		$skillDamage = ($baseDamage + $equipmentBonus) * $skill['damageMultiplier'];
		$totalDamage = round($skillDamage);

		// --- StatusEffekt-Chancen pro Klasse und Skill ---
		// Magier-Skills: Status-Effekte!
		if ($this->playerClass === "Magier") {
			if ($skillKey === 'feuerball' && rand(1,100) <= 20) {
				$staerke = round(($this->damage + $this->getEquipmentDamageBonus()) * 0.4);
				$enemy->addStatusEffect(new \App\Model\StatusEffect('burn', 3, $staerke));
			}
			if ($skillKey === 'eisstrahl' && rand(1,100) <= 20) {
				$enemy->addStatusEffect(new \App\Model\StatusEffect('paralyze', 2));
			}
			if ($skillKey === 'kettenblitz' && rand(1,100) <= 20) {
				$enemy->addStatusEffect(new \App\Model\StatusEffect('stun', 2));
			}
		}
		// Krieger & Schütze: Bluten auch bei Skills
		if (
				($this->playerClass === "Schütze" || $this->playerClass === "Krieger")
				&& rand(1,100) <= 20
				) {
					$staerke = round(($this->damage + $this->getEquipmentDamageBonus()) * 0.3);
					$enemy->addStatusEffect(new \App\Model\StatusEffect('bleed', 3, $staerke));
				}

				$actualDamage = $enemy->takeDamage($totalDamage);
				$this->energy -= $skill['energyCost'];
				return [
						'success' => true,
						'damage' => $actualDamage,
						'skillName' => $skill['name']
				];
	}

	// --- STATUS-FUNKTIONEN ---
	public function addStatusEffect(\App\Model\StatusEffect $effect) {
		foreach ($this->statusEffects as $existing) {
			if ($existing->type === $effect->type) {
				$existing->duration = max($existing->duration, $effect->duration);
				$existing->strength = max($existing->strength, $effect->strength);
				return;
			}
		}
		$this->statusEffects[] = $effect;
	}

	public function processStatusEffects(&$combatLog) {
		$blocked = false;
		// Erst restrictive Effekte (Stun/Paralyze) loggen und blockieren
		foreach ($this->statusEffects as $key => $effect) {
			if ($effect->type === 'stun' || $effect->type === 'paralyze') {
				$combatLog[] = "{$this->name} ist " .
				($effect->type === 'stun' ? "betäubt" : "gelähmt") .
				" und kann diesen Zug nicht handeln!";
				$blocked = true;
			}
		}
		// Dann DOT-Schaden (Burn, Bleed)
		foreach ($this->statusEffects as $key => $effect) {
			if ($effect->type === 'burn' || $effect->type === 'bleed') {
				$this->hp = max(0, $this->hp - $effect->strength);
				$effectName = $effect->type === 'burn' ? "Verbrennung" : "Blutung";
				$combatLog[] = "{$this->name} erleidet {$effect->strength} Schaden durch {$effectName}!";
			}
			$effect->duration--;
			if ($effect->duration <= 0) {
				unset($this->statusEffects[$key]);
			}
		}
		$this->statusEffects = array_values($this->statusEffects);
		return $blocked;
	}

	public function regenerateEnergy() {
		$regenAmount = ceil($this->maxEnergy * 0.05); // 5% Regeneration
		$this->energy = min($this->maxEnergy, $this->energy + $regenAmount);
		return $regenAmount;
	}

	public function takeDamage($damage) {
		// Check for dodge
		$totalDodgeChance = $this->dodgeChance + $this->getEquipmentDodgeBonus();
		if (rand(1, 100) <= $totalDodgeChance) {
			return 0; // Attack dodged
		}

		// Check for block
		$totalBlockChance = $this->blockChance + $this->getEquipmentBlockBonus();
		if (rand(1, 100) <= $totalBlockChance) {
			$damage = floor($damage / 2); // Block reduces damage
		}

		$defenseReduction = $this->getEquipmentDefenseBonus();
		$actualDamage = max(1, $damage - $defenseReduction);
		$this->hp = max(0, $this->hp - $actualDamage); // Prevent negative HP
		return $actualDamage;
	}

	public function getEquipmentDamageBonus() {
		$bonus = 0;
		foreach ($this->equipment as $item) {
			if ($item && isset($item['damage'])) {
				$bonus += $item['damage'];
			}
		}
		return $bonus;
	}

	public function getEquipmentDefenseBonus() {
		$bonus = 0;
		foreach ($this->equipment as $item) {
			if ($item && isset($item['defense'])) {
				$bonus += $item['defense'];
			}
		}
		return $bonus;
	}

	public function gainExp($exp) {
		$this->exp += $exp;

		if ($this->exp < 0) {
			$this->exp = 0;
		}

		$levelUps = 0;
		// Sammle die LevelUp-Ergebnisse, wenn benötigt
		while ($this->exp >= $this->expToNext) {
			$this->levelUp();
			$levelUps++;
		}

		return $levelUps;
	}

	public function levelUp() {
		// Ziehe benötigte Erfahrung für aktuelles Level ab - vorher prüfen, dass exp groß genug
		if ($this->exp < $this->expToNext) {
			return false; // Nicht genug EXP => kein LevelUp
		}

		$this->exp -= $this->expToNext;

		$this->level++;

		$this->expToNext = $this->level * 100;

		// Stats erhöhen
		$hpIncrease = rand(5, 15);
		$this->maxHp += $hpIncrease;
		$this->hp = $this->maxHp; // Vollheilung beim Level-Up
		$this->damage += rand(2, 5);
		$this->defense += rand(1, 2);

		$energyIncrease = rand(5, 10);
		$this->maxEnergy += $energyIncrease;
		$this->energy = $this->maxEnergy; // Energie auffüllen beim Level-Up

		// Negative EXP vermeiden (sollte nicht eintreten)
		if ($this->exp < 0) {
			$this->exp = 0;
		}

		return [
				'hpIncrease' => $hpIncrease,
				'energyIncrease' => $energyIncrease
		];
	}

	public function heal() {
		$healCost = $this->level * 10;
		if ($this->gold >= $healCost) {
			$this->gold -= $healCost;
			$this->hp = $this->maxHp;
			return true;
		}
		return false;
	}

	public function restoreEnergy() {
		$energyCost = round($this->level * 5);
		if ($this->gold >= $energyCost) {
			$this->gold -= $energyCost;
			$this->energy = $this->maxEnergy;
			return true;
		}
		return false;
	}

	public function equipItem($item, $slot) {
		if (!array_key_exists($slot, $this->equipment)) {
			return false;
		}

		if (!isset($item['name'])) {
			if (count($item) === 1) {
				$keys = array_keys($item);
				$name = $keys[0];
				$item = $item[$name];
				$item['name'] = $name;
			} else {
				$item['name'] = 'Unbekannt';
			}
		}

		$this->equipment[$slot] = $item;
		return true;
	}

	public function hasStatus($type) {
		foreach ($this->statusEffects as $effect) {
			if ($effect->type === $type) return true;
	}
	return false;
	}

	public function canAct() {
		foreach ($this->statusEffects as $effect) {
			if ($effect->type === 'stun' || $effect->type === 'paralyze') return false;
		}
		return true;
	}

	public function isItemBetter($slot, $shopItem) {
		// ShopItem benötigt den Namen, falls noch nicht vorhanden
		if (!isset($shopItem['name'])) return true;

		$current = $this->equipment[$slot] ?? null;
		if (!$current) return true; // Kein Item? Immer besser.

		// Wenn aktuelles Item exakt gleich (Name):
		if (isset($current['name']) && $current['name'] === $shopItem['name']) return false;

		// "Item-Wert" definieren anhand typischer Item-Stats
		$keys = ['damage','defense','block','dodge'];
		$shopValue = 0; $currentValue = 0;
		foreach($keys as $k) {
			$shopValue   += $shopItem[$k] ?? 0;
			$currentValue+= $current[$k] ?? 0;
		}
		return $shopValue > $currentValue;
	}
}