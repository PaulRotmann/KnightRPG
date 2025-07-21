<?php

namespace App\View;

function renderEquipment($player) {
	if ($player === null) { return ''; }

	$slotNames = [
			'helmet' => 'Helm',
			'armor' => 'Rüstung',
			'gloves' => 'Handschuhe',
			'boots' => 'Schuhe',
			'weapon' => 'Waffe',
			'ring' => 'Ring',
			'amulet' => 'Amulett'
	];
	$slotIcons = [
			'helmet' => '⛑️',
			'armor' => '🦺',
			'gloves' => '🧤',
			'boots' => '🥾',
			'weapon' => '⚔️',
			'ring' => '💍',
			'amulet' => '📿'
	];

	$html = '<div class="equipment">
        <h3>Ausrüstung</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">';

	foreach ($player->equipment as $slot => $item) {
		$icon = isset($slotIcons[$slot]) ? $slotIcons[$slot] : '';
		$itemName = $item ? array_keys($item)[0] : 'Leer';
		$stats = '';
		if ($item) {
			if (isset($item['damage'])) $stats .= ' (+' . $item['damage'] . ' Schaden)';
			if (isset($item['defense'])) $stats .= ' (+' . $item['defense'] . ' Verteidigung)';
		}
		$displayName = $item ? $icon . ' ' . $itemName : $icon . ' Leer';
		$html .= '<div><strong>' . $slotNames[$slot] . ':</strong><br>' . $displayName . $stats . '</div>';
	}

	$html .= '</div></div>';
	return $html;
}


function renderClassSelection() {
	$html = '<div>
        <h2>Wähle deine Klasse</h2>
        <p>Wähle eine Klasse für deinen Helden:</p>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0;">
            <div style="border: 2px solid #4CAF50; padding: 20px; border-radius: 10px; text-align: center;">
                <h3 style="color: #4CAF50;">Krieger</h3>
                <div style="font-size: 3em;">⚔️</div>
                <p>Der Krieger ist ein meisterhafter Nahkämpfer mit hoher Verteidigung und Lebenspunkten.</p>
                <ul style="text-align: left;">
                    <li>Hohe HP: 100</li>
                    <li>Gute Verteidigung: 5</li>
                    <li>Solider Schaden: 15</li>
                    <li>Normale Energie: 100</li>
                </ul>
                <div>
                    <input type="text" id="warrior-name" placeholder="Name deines Kriegers" value="Ritter" style="margin-bottom: 10px; padding: 8px; width: 100%;">
                    <button onclick="gameAction(\'select_class\', {name: document.getElementById(\'warrior-name\').value, class: \'Krieger\'})" style="width: 100%; background: #4CAF50;">Krieger wählen</button>
                </div>
            </div>

            <div style="border: 2px solid #2196F3; padding: 20px; border-radius: 10px; text-align: center;">
                <h3 style="color: #2196F3;">Schütze</h3>
                <div style="font-size: 3em;">🏹</div>
                <p>Der Schütze ist ein geschickter Fernkämpfer mit hohem Schaden und mittlerer Verteidigung.</p>
                <ul style="text-align: left;">
                    <li>Mittlere HP: 90</li>
                    <li>Leichte Verteidigung: 4</li>
                    <li>Hoher Schaden: 18</li>
                    <li>Gute Energie: 120</li>
                </ul>
                <div>
                    <input type="text" id="archer-name" placeholder="Name deines Schützen" value="Bogenschütze" style="margin-bottom: 10px; padding: 8px; width: 100%;">
                    <button onclick="gameAction(\'select_class\', {name: document.getElementById(\'archer-name\').value, class: \'Schütze\'})" style="width: 100%; background: #2196F3;">Schütze wählen</button>
                </div>
            </div>

            <div style="border: 2px solid #9C27B0; padding: 20px; border-radius: 10px; text-align: center;">
                <h3 style="color: #9C27B0;">Magier</h3>
                <div style="font-size: 3em;">🧙‍♂️</div>
                <p>Der Magier beherrscht mächtige Zauber mit sehr hohem Schaden, aber geringer Verteidigung.</p>
                <ul style="text-align: left;">
                    <li>Niedrige HP: 80</li>
                    <li>Schwache Verteidigung: 3</li>
                    <li>Sehr hoher Schaden: 20</li>
                    <li>Hohe Energie: 150</li>
                </ul>
                <div>
                    <input type="text" id="mage-name" placeholder="Name deines Magiers" value="Zauberer" style="margin-bottom: 10px; padding: 8px; width: 100%;">
                    <button onclick="gameAction(\'select_class\', {name: document.getElementById(\'mage-name\').value, class: \'Magier\'})" style="width: 100%; background: #9C27B0;">Magier wählen</button>
                </div>
            </div>
        </div>
    </div>';

	return $html;
}

function renderHome($game) {
	$healCost = $game->player->level * 10;
	$canHeal = $game->player->gold >= $healCost && $game->player->hp < $game->player->maxHp;
	$healButton = $canHeal ?
	'<button onclick="gameAction(\'heal\')">Heilen (' . $healCost . ' Gold)</button>' :
	'<button style="opacity: 0.5; cursor: not-allowed;">Heilen (' . $healCost . ' Gold) - ' .
	($game->player->hp >= $game->player->maxHp ? 'Bereits vollständig geheilt' : 'Nicht genug Gold') . '</button>';

	// Add energy restoration option
	$energyCost = round($game->player->level * 5);
	$canRestoreEnergy = $game->player->gold >= $energyCost && $game->player->energy < $game->player->maxEnergy;
	$energyButton = $canRestoreEnergy ?
	'<button onclick="gameAction(\'restore_energy\')">Energie aufladen (' . $energyCost . ' Gold)</button>' :
	'<button style="opacity: 0.5; cursor: not-allowed;">Energie aufladen (' . $energyCost . ' Gold) - ' .
	($game->player->energy >= $game->player->maxEnergy ? 'Energie bereits voll' : 'Nicht genug Gold') . '</button>';

	return '<div>
        <h2>Willkommen, tapferer Ritter!</h2>
        <p>Was möchtest du tun?</p>
        <button onclick="gameAction(\'fight\')">Monster auswählen</button>
        <button onclick="gameAction(\'shop\')">Shop besuchen</button>
        ' . $healButton . '
        ' . $energyButton . '
    </div>';
}

function renderMonsterSelection($game) {
	$enemyTypes = $game->getEnemyTypes();
	$html = '<div>
        <h2>Wähle deinen Gegner</h2>
        <p>Gegen welches Monster möchtest du kämpfen?</p>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0;">';

	foreach ($enemyTypes as $enemyName => $stats) {
		$difficulty = '';
		$color = '';

		// Schwierigkeitsgrad basierend auf HP bestimmen
		if ($stats['hp'][1] <= 30) {
			$difficulty = '⭐ Leicht';
			$color = '#4CAF50';
		} elseif ($stats['hp'][1] <= 60) {
			$difficulty = '⭐⭐ Mittel';
			$color = '#FF9800';
		} elseif ($stats['hp'][1] <= 120) {
			$difficulty = '⭐⭐⭐ Schwer';
			$color = '#f44336';
		} else {
			$difficulty = '⭐⭐⭐⭐ Extrem';
			$color = '#9C27B0';
		}

		$html .= '<div style="border: 2px solid ' . $color . '; padding: 15px; border-radius: 10px; background: white;">
            <h3 style="color: ' . $color . '; margin: 0 0 10px 0;">' . $enemyName . '</h3>
            <div style="font-size: 0.9em; margin-bottom: 10px;">
                <div><strong>Schwierigkeit:</strong> ' . $difficulty . '</div>
                <div><strong>HP:</strong> ' . $stats['hp'][0] . ' - ' . $stats['hp'][1] . '</div>
                <div><strong>Schaden:</strong> ' . $stats['damage'][0] . ' - ' . $stats['damage'][1] . '</div>
                <div><strong>EXP Belohnung:</strong> ' . $stats['exp'][0] . ' - ' . $stats['exp'][1] . '</div>
                <div><strong>Gold Belohnung:</strong> ' . $stats['gold'][0] . ' - ' . $stats['gold'][1] . '</div>
            </div>
            <button onclick="gameAction(\'select_enemy\', {enemy: \'' . $enemyName . '\'})"
                    style="width: 100%; background: ' . $color . '; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">
                Kämpfen gegen ' . $enemyName . '
            </button>
        </div>';
	}

	$html .= '</div>
        <button onclick="gameAction(\'home\')">Zurück zur Hauptseite</button>
    </div>';

	return $html;
}

function renderAvatar($type, $name, $hp, $maxHp, $energy = null, $maxEnergy = null, $lastDmg = null) {
	$fileSafeName = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $name));
	$fileSafeName = preg_replace('/[^a-z0-9]/', '', $fileSafeName);
	$avatarFile = "/assets/avatars/{$fileSafeName}.png";
	if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $avatarFile)) {
		$avatarFile = "/assets/avatars/default.png";
	}
	$hpPercent = max(0, min(100, round(($hp / $maxHp) * 100)));
	$energyBar = '';
	if ($energy !== null && $maxEnergy !== null) {
		$energyPercent = max(0, min(100, round(($energy / $maxEnergy) * 100)));
		// Energie-Balken UNTER dem Bild, als Block
		$energyBar = '<div class="energy-bar" style="width:100px;height:10px;background:#eee;border-radius:6px;box-shadow:0 0 5px #0002;margin:2px auto 0;">
            <div style="width:'.$energyPercent.'%;height:100%;background:#FFC107;border-radius:6px;"></div>
        </div>';
	}
	$damageNumber = '';
	if ($lastDmg !== null && $lastDmg > 0) {
		$damageNumber = '<div class="damage-number" style="position:absolute;top:35px;left:35px;font-size:1.4em;font-weight:bold;color:#f44336;animation:damage-float 1.2s forwards;pointer-events:none;">
            -'.$lastDmg.'
        </div>';
	}
	// NEU: HP-Balken unter dem Bild, als Block
	$hpBar = '<div class="hp-bar" style="width:100px;height:10px;background:#eee;border-radius:6px;box-shadow:0 0 5px #0002;margin:8px auto 0;">
            <div style="width:'.$hpPercent.'%;height:100%;background:#4CAF50;border-radius:6px;"></div>
        </div>';
	return '
    <div class="avatar-'.$type.'" style="position:relative;display:inline-block;width:100px;margin:5px 15px 5px 0;text-align:center;">
        <img src="'.$avatarFile.'" alt="'.$name.'" style="width:100px;height:100px;border-radius:10px;border:2px solid #888;box-shadow:1px 2px 6px #2222;display:block;margin:0 auto;">
        '.$damageNumber.'
        '.$hpBar.'
        '.$energyBar.'
    </div>';
}

function renderCombat($game) {
	$enemy = $game->currentEnemy;

	$html = '<div style="display:flex;flex-direction:row;align-items:center;justify-content:center;margin-bottom:20px;">'
			. renderAvatar('player', $game->player->playerClass, $game->player->hp, $game->player->maxHp, $game->player->energy, $game->player->maxEnergy, $game->lastEnemyDmg)
			. renderAvatar('enemy', $enemy->name, $enemy->hp, $enemy->maxHp, null, null, $game->lastPlayerDmg)
			. '</div>';

			$html .= '<div class="enemy-stats" style="text-align:center;margin-bottom:10px;">
    		<strong>Monster: ' . $enemy->name . '</strong> |
    		<strong>Ausweichen:</strong> ' . $enemy->dodgeChance . '% |
    		<strong>Parieren:</strong> ' . $enemy->blockChance . '%</div>';

			$html .= '<div class="combat-log">';
			foreach ($game->combatLog as $log) {
				$html .= '<div>' . $log . '</div>';
			}
			$html .= '</div>';

			if ($enemy->isAlive()) {
				$html .= '<div style="margin-bottom: 15px;">
            <h3>Aktionen:</h3>
            <button onclick="gameAction(\'attack\')" style="background-color: #4CAF50;">Angreifen</button>';

				// Fertigkeiten-Buttons hinzufügen wenn verfügbar
				foreach ($game->player->skills as $key => $skill) {
					if ($game->player->level >= $skill['unlockLevel']) {
						$disabled = $game->player->energy < $skill['energyCost'];
						$buttonStyle = $disabled ?
						'opacity: 0.5; cursor: not-allowed; background-color: #673AB7;' :
						'background-color: #673AB7;';

						$html .= '<button
                    onclick="' . (!$disabled ? "gameAction('use_skill', {skillKey: '$key'})" : '') . '"
                    style="' . $buttonStyle . '"
                    title="' . $skill['description'] . ' (Kostet ' . $skill['energyCost'] . ' Energie)">' .
                    $skill['name'] . ' (' . $skill['energyCost'] . ' EP)
                </button>';
					}
				}

				$html .= '</div>';
			}

			$html .= '<button onclick="gameAction(\'home\')">Zurück zur Hauptseite</button>';

			return $html;
}

function renderShop($game) {
	$shopItems = $game->getShopItems();
	$slotNames = [
			'helmet' => 'Helme',
			'armor' => 'Rüstungen',
			'gloves' => 'Handschuhe',
			'boots' => 'Schuhe',
			'weapon' => 'Waffen',
			'ring' => 'Ringe',
			'amulet' => 'Amulette'
	];
	$slotIcons = [
			'helmet' => '⛑️',
			'armor' => '🦺',
			'gloves' => '🧤',
			'boots' => '🥾',
			'weapon' => '⚔️',
			'ring' => '💍',
			'amulet' => '📿'
	];

	$html = '<div><h2>Shop</h2><p>Dein Gold: ' . $game->player->gold . '</p>';

	foreach ($shopItems as $slot => $items) {
		$icon = $slotIcons[$slot] ?? '';
		$html .= '<h3>' . $icon . ' ' . $slotNames[$slot] . '</h3>';
		foreach ($items as $itemName => $item) {
			$stats = '';
			if (isset($item['damage'])) $stats .= ' (+' . $item['damage'] . ' Schaden)';
			if (isset($item['defense'])) $stats .= ' (+' . $item['defense'] . ' Verteidigung)';
			$canBuy = $game->player->gold >= $item['price'];
			$buttonStyle = $canBuy ? '' : 'style="opacity: 0.5; cursor: not-allowed;"';
			$html .= '<div class="shop-item">'
					. '<strong>' . $icon . ' ' . $itemName . '</strong> - ' . $item['price'] . ' Gold' . $stats .
					'<button ' . $buttonStyle . ' onclick="' . ($canBuy ? 'gameAction(\'buy\', {slot: \'' . $slot . '\', item: \'' . $itemName . '\'})' : '') . '">Kaufen</button>
            </div>';
		}
	}
	$html .= '<button onclick="gameAction(\'home\')">Zurück zur Hauptseite</button></div>';
	return $html;
}


function renderGameOver($game) {
	$html = '<div style="text-align: center; background: #ffebee; padding: 30px; border-radius: 10px; border: 2px solid #f44336;">
        <h2 style="color: #d32f2f; font-size: 2.5em;">💀 GAME OVER 💀</h2>
        <p style="font-size: 1.2em; margin: 20px 0;">Dein tapferer Ritter ist im Kampf gefallen!</p>

        <div style="background: #fff; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3>Endstatistiken:</h3>
            <div style="font-size: 1.1em;">
                <div>Erreichtes Level: <strong>' . $game->player->level . '</strong></div>
                <div>Gesammeltes Gold: <strong>' . $game->player->gold . '</strong></div>
                <div>Gesammelte Erfahrung: <strong>' . $game->player->exp . '</strong></div>
                <div>Maximale HP: <strong>' . $game->player->maxHp . '</strong></div>
            </div>
        </div>

        <div class="combat-log" style="margin: 20px 0;">
            <h4>Letzte Kampfmomente:</h4>';

	foreach ($game->combatLog as $log) {
		$html .= '<div>' . $log . '</div>';
	}

	$html .= '</div>

        <button onclick="gameAction(\'restart\')" style="font-size: 1.2em; padding: 15px 30px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">Neues Spiel starten</button>
    </div>';

	return $html;
}

function renderGameContent($game) {
	switch ($game->gameState) {
		case 'class_selection':
			return renderClassSelection();
		case 'home':
			return renderHome($game);
		case 'combat':
			return renderCombat($game);
		case 'shop':
			return renderShop($game);
		case 'monster_selection':
			return renderMonsterSelection($game);
		case 'gameover':
			return renderGameOver($game);
		default:
			return renderHome($game);
	}
}

function renderGame($game) {
	$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RPG Browsergame</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .player-stats { background: #e8f4f8; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .combat-log { background: #f8f8f8; padding: 10px; border-radius: 5px; height: 150px; overflow-y: auto; margin: 10px 0; }
        .enemy-stats { background: #f8e8e8; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .shop-item { background: #f0f8f0; padding: 10px; margin: 5px 0; border-radius: 5px; }
        .equipment { background: #fff8e8; padding: 10px; border-radius: 5px; margin: 10px 0; }
        button { padding: 10px 20px; margin: 5px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
        .danger { background: #f44336; }
        .danger:hover { background: #da190b; }
        .avatar-player, .avatar-enemy { margin: 5px 15px; }
        .hp-bar, .energy-bar { box-shadow: 0 0 5px #0002; }
        .damage-number {
            animation: damage-float 1.2s forwards;
            pointer-events: none;
        }
        @keyframes damage-float {
            0% {
                opacity:1; transform:translateY(0) scale(1.1);
                text-shadow:1px 2px 6px #ca2;
            }
            70% { opacity:0.95;}
            100% {
                opacity:0; transform:translateY(-32px) scale(1.25);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>RPG Browsergame</h1>
        ' . renderPlayerStats($game->player) . '
        ' . renderEquipment($game->player) . '
        ' . renderGameContent($game) . '
    </div>

    <script>
        async function gameAction(action, data = {}) {
            const response = await fetch("/action", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action, ...data })
            });
            const result = await response.json();
            if (result.success) {
                document.body.innerHTML = result.html;
            }
        }
    </script>
</body>
</html>';

	return $html;
}

function renderPlayerStats($player) {
	if ($player === null) {
		return '<div class="player-stats">
                    <h2>Kein Spieler gefunden</h2>
                    <p>Wähle eine Klasse, um das Spiel zu beginnen.</p>
                </div>';
	}

	$hpBar = ($player->hp / $player->maxHp) * 100;
	$expBar = ($player->exp / $player->expToNext) * 100;
	$energyBar = ($player->energy / $player->maxEnergy) * 100;

	// Item-Bonus abfragen, falls Methoden vorhanden:
	$dodge = $player->dodgeChance;
	$block = $player->blockChance;
	if (method_exists($player, 'getEquipmentDodgeBonus')) {
		$dodge += $player->getEquipmentDodgeBonus();
	}
	if (method_exists($player, 'getEquipmentBlockBonus')) {
		$block += $player->getEquipmentBlockBonus();
	}
	// HP, Energie und EXP Balken in Prozent
	return '<div class="player-stats">
            <h2>' . $player->name . ' (Level ' . $player->level . ')</h2>
            <div>HP: ' . $player->hp . '/' . $player->maxHp . ' <div style="width: 200px; height: 20px; background: #ddd; border-radius: 10px; display: inline-block;"><div style="width: ' . $hpBar . '%; height: 100%; background: #4CAF50; border-radius: 10px;"></div></div></div>
            <div>Energie: ' . $player->energy . '/' . $player->maxEnergy . ' <div style="width: 200px; height: 20px; background: #ddd; border-radius: 10px; display: inline-block;"><div style="width: ' . $energyBar . '%; height: 100%; background: #FFC107; border-radius: 10px;"></div></div></div>
            <div>EXP: ' . $player->exp . '/' . $player->expToNext . ' <div style="width: 200px; height: 20px; background: #ddd; border-radius: 10px; display: inline-block;"><div style="width: ' . $expBar . '%; height: 100%; background: #2196F3; border-radius: 10px;"></div></div></div>
            <div>Schaden: ' . ($player->damage + $player->getEquipmentDamageBonus()) . ' | Verteidigung: ' . ($player->defense + $player->getEquipmentDefenseBonus()) . '</div>
            <div><strong>Ausweichen:</strong> ' . $dodge . '% | <strong>Parieren:</strong> ' . $block . '%</div>
            <div>Gold: ' . $player->gold . '</div>
        </div>';
}
