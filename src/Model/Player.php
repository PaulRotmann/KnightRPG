<?php

namespace App\Model;

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
				break;
			case "Schütze":
				$this->hp = 90;
				$this->maxHp = 90;
				$this->damage = 18;
				$this->defense = 4;
				$this->energy = 120;
				$this->maxEnergy = 120;
				break;
			case "Krieger":
			default:
				$this->hp = 100;
				$this->maxHp = 100;
				$this->damage = 15;
				$this->defense = 5;
				$this->energy = 100;
				$this->maxEnergy = 100;
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
				'damageMultiplier' => 2.2,
				'description' => 'Ein mächtiger Feuerball, der großen Schaden verursacht.'
						],
						'eisstrahl' => [
						'name' => 'Eisstrahl',
						'unlockLevel' => 8,
						'energyCost' => 50,
						'damageMultiplier' => 2.8,
						'description' => 'Ein Strahl aus Eis, der den Gegner verlangsamt und schadet.'
								],
								'kettenblitz' => [
								'name' => 'Kettenblitz',
								'unlockLevel' => 13,
								'energyCost' => 70,
								'damageMultiplier' => 3.5,
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
				'damageMultiplier' => 1.8,
				'description' => 'Ein präziser Schuss an eine verwundbare Stelle.'
						],
						'mehrfachschuss' => [
						'name' => 'Mehrfachschuss',
						'unlockLevel' => 8,
						'energyCost' => 40,
						'damageMultiplier' => 2.4,
						'description' => 'Mehrere Pfeile werden gleichzeitig abgefeuert.'
								],
								'explosivpfeil' => [
								'name' => 'Explosivpfeil',
								'unlockLevel' => 13,
								'energyCost' => 55,
								'damageMultiplier' => 3.2,
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
				'damageMultiplier' => 2.0,
				'description' => 'Ein mächtiger Schlag, der doppelten Schaden verursacht.'
						],
						'sprungangriff' => [
						'name' => 'Sprungangriff',
						'unlockLevel' => 8,
						'energyCost' => 45,
						'damageMultiplier' => 2.5,
						'description' => 'Ein Sprung auf den Gegner mit verheerender Wucht.'
								],
								'wirbelwind' => [
								'name' => 'Wirbelwind',
								'unlockLevel' => 13,
								'energyCost' => 60,
								'damageMultiplier' => 3.0,
								'description' => 'Eine rotierende Attacke, die dreifachen Schaden verursacht.'
										]
										];
				break;
		}
	}

	public function attack($enemy) {
		$baseDamage = rand($this->damage - 5, $this->damage + 5);
		$equipmentBonus = $this->getEquipmentDamageBonus();
		$totalDamage = $baseDamage + $equipmentBonus;

		$enemy->takeDamage($totalDamage);
		return $totalDamage;
	}

	public function useSkill($skillKey, $enemy) {
		$skill = $this->skills[$skillKey];

		if ($this->energy < $skill['energyCost']) {
			return ['success' => false, 'message' => 'Nicht genug Energie!'];
		}

		$baseDamage = rand($this->damage - 5, $this->damage + 5);
		$equipmentBonus = $this->getEquipmentDamageBonus();
		$skillDamage = ($baseDamage + $equipmentBonus) * $skill['damageMultiplier'];
		$totalDamage = round($skillDamage);

		$enemy->takeDamage($totalDamage);
		$this->energy -= $skill['energyCost'];

		return [
				'success' => true,
				'damage' => $totalDamage,
				'skillName' => $skill['name']
		];
	}

	public function regenerateEnergy() {
		$regenAmount = ceil($this->maxEnergy * 0.05); // 5% Regeneration
		$this->energy = min($this->maxEnergy, $this->energy + $regenAmount);
		return $regenAmount;
	}

	public function takeDamage($damage) {
		$defenseReduction = $this->getEquipmentDefenseBonus();
		$actualDamage = max(1, $damage - $defenseReduction);
		$this->hp -= $actualDamage;
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

		// Ensure exp is never negative
		if ($this->exp < 0) {
			$this->exp = 0;
		}

		if ($this->exp >= $this->expToNext) {
			$this->levelUp();
		}
	}

	public function levelUp() {
		$this->level++;

		// Fix: Ensure exp is never negative
		if ($this->exp < 0) {
			$this->exp = 0;
		}

		$this->expToNext = $this->level * 100;

		// Stats erhöhen
		$hpIncrease = rand(15, 25);
		$this->maxHp += $hpIncrease;
		$this->hp = $this->maxHp; // Vollheilung beim Level-Up
		$this->damage += rand(3, 7);
		$this->defense += rand(2, 4);

		// Energy beim Level-Up erhöhen
		$energyIncrease = rand(5, 10);
		$this->maxEnergy += $energyIncrease;
		$this->energy = $this->maxEnergy; // Energie auffüllen beim Level-Up

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
		$this->equipment[$slot] = $item;
	}
}