<?php

namespace App\Model;

class Enemy {
	public $name;
	public $hp;
	public $maxHp;
	public $damage;
	public $expReward;
	public $goldReward;

	public function __construct($name, $hp, $damage, $expReward, $goldReward) {
		$this->name = $name;
		$this->hp = $hp;
		$this->maxHp = $hp;
		$this->damage = $damage;
		$this->expReward = $expReward;
		$this->goldReward = $goldReward;
	}

	public function attack($player) {
		$damage = rand($this->damage - 3, $this->damage + 3);
		return $player->takeDamage($damage);
	}

	public function takeDamage($damage) {
		$this->hp -= $damage;
	}

	public function isAlive() {
		return $this->hp > 0;
	}
}