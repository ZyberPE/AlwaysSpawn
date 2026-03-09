<?php

declare(strict_types=1);

namespace AlwaysSpawn;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\Position;
use pocketmine\Server;

use pocketmine\math\Vector3;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\sound\XpCollectSound;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function teleportToSpawn(Player $player): void {

        $config = $this->getConfig()->get("spawn");

        $world = Server::getInstance()->getWorldManager()->getWorldByName($config["world"]);

        if($world === null){
            $this->getLogger()->error("Spawn world not found!");
            return;
        }

        $pos = new Position(
            (float)$config["x"],
            (float)$config["y"],
            (float)$config["z"],
            $world
        );

        $player->teleport($pos);

        // Guardian Angel Effect
        if($this->getConfig()->getNested("effects.guardian-angel") === true){

            for($i = 0; $i < 20; $i++){

                $particlePos = new Vector3(
                    $pos->x + mt_rand(-2,2) / 2,
                    $pos->y + mt_rand(0,2),
                    $pos->z + mt_rand(-2,2) / 2
                );

                $world->addParticle($particlePos, new HappyVillagerParticle());
                $world->addParticle($particlePos, new FlameParticle());
            }

            $world->addSound($pos, new XpCollectSound());
        }
    }

    public function onJoin(PlayerJoinEvent $event): void {

        $player = $event->getPlayer();

        $this->teleportToSpawn($player);

        $player->sendMessage($this->getConfig()->getNested("messages.join-teleport"));
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if($command->getName() === "spawn"){

            if(!$sender instanceof Player){
                $sender->sendMessage("Run this command in-game.");
                return true;
            }

            $this->teleportToSpawn($sender);

            $sender->sendMessage($this->getConfig()->getNested("messages.teleport"));

            return true;
        }

        return false;
    }
}
