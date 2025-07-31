<?php

namespace App\Model;

use App\Model\StatusEffect;

class Enemy {
	public $name;
	public $hp;
	public $maxHp;
	public $damage;
	public $expReward;
	public $goldReward;
	public $dodgeChance; // % Wahrscheinlichkeit auszuweichen
	public $blockChance; // % Wahrscheinlichkeit zu parieren
	public $specialAttack = null;

	public $statusEffects = []; // Array von StatusEffect-Objekten

	public function __construct($name, $hp, $damage, $expReward, $goldReward, $dodgeChance = 0, $blockChance = 0, $specialAttack = null) {
		$this->name = $name;
		$this->hp = $hp;
		$this->maxHp = $hp;
		$this->damage = $damage;
		$this->expReward = $expReward;
		$this->goldReward = $goldReward;
		$this->dodgeChance = $dodgeChance;
		$this->blockChance = $blockChance;
		$this->specialAttack = $specialAttack;
	}

	public function attack($player) {
		if (!$this->canAct()) {
			return 0;
		}
		$damage = max(1, rand($this->damage - 3, $this->damage + 3));
		return $player->takeDamage($damage);
	}

	public function takeDamage($damage) {
		// Check dodge
		if (rand(1, 100) <= $this->dodgeChance) {
			return 0; // Dodged
		}

		// Check block
		if (rand(1, 100) <= $this->blockChance) {
			$damage = floor($damage / 2); // Reduce damage on block
		}

		$damage = max(0, $damage);
		$this->hp -= $damage;
		return $damage;
	}

	public function isAlive() {
		return $this->hp > 0;
	}

	// --- STATUS-EFFEKT-FUNKTIONEN ---
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
		foreach ($this->statusEffects as $key => $effect) {
			switch ($effect->type) {
				case 'burn':
					$this->hp -= $effect->strength;
					$combatLog[] = "{$this->name} erleidet {$effect->strength} Feuerschaden durch Verbrennung!";
					break;
				case 'bleed':
					$this->hp -= $effect->strength;
					$combatLog[] = "{$this->name} blutet und verliert {$effect->strength} Lebenspunkte!";
					break;
				case 'paralyze':
					$combatLog[] = "{$this->name} ist gelähmt und kann nicht angreifen!";
					break;
				case 'stun':
					$combatLog[] = "{$this->name} ist betäubt und kann diesen Zug nicht handeln!";
					break;
			}
			$effect->duration--;
			if ($effect->duration <= 0) {
				unset($this->statusEffects[$key]);
			}
		}
		$this->statusEffects = array_values($this->statusEffects);
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
}
