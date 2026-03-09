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

use pocketmine\world\particle\EnchantParticle;
use pocketmine\world\particle\HappyVillagerParticle;
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

        if($this->getConfig()->getNested("effects.guardian-angel") === true){

            for($i = 0; $i < 15; $i++){
                $world->addParticle($pos, new EnchantParticle());
                $world->addParticle($pos, new HappyVillagerParticle());
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
