<?php

namespace xeonch\afksystem\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use xeonch\afksystem\AfkSystem as Main;
use xeonch\afksystem\forms\AfkForm;

class AfkCommand extends BaseCommand
{
    /**
     * @throws ArgumentOrderException
     */
    public function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("reason", true));
        $this->addConstraint(new InGameRequiredConstraint($this));
        $this->setPermission("afksystem.command.afk");
    }

    public function getPermission(): string
    {
        return "afksystem.command.afk";
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$this->testPermission($sender)) return;
        if (!empty($args["reason"])) {
            $reason = $args["reason"];
            $session = Main::getInstance()->getSession($sender);

            if ($session->getAFK()) {
                $sender->sendMessage(TextFormat::colorize(Main::getInstance()->getConfig()->get("msg")["already-afk"]));
                return;
            }

            if (strlen($reason) > Main::getInstance()->getConfig()->get("settings")["reason-letter-length"]) {
                $sender->sendMessage(TextFormat::colorize(Main::getInstance()->getConfig()->get("msg")["reason-too-long"]));
                return;
            }

            $session->setAFK(true);
            $session->setReason($reason);
            return;
        }
        (new AfkForm())->open($sender);
    }
}