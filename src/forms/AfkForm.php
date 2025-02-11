<?php

namespace xeonch\afksystem\forms;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\CustomForm;
use xeonch\afksystem\AfkSystem as Main;

class AfkForm
{

    /**
     * @param Player $player
     * @return void
     */
    public function open(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) return;
            $reason = $data[1];
            $session = Main::getInstance()->getSession($player);

            if ($session->getAFK()) {
                $player->sendMessage(TextFormat::colorize(Main::getInstance()->getConfig()->get("msg")["already-afk"]));
                return;
            }

            if (strlen($reason) > Main::getInstance()->getConfig()->get("settings")["reason-letter-length"]) {
                $player->sendMessage(TextFormat::colorize(Main::getInstance()->getConfig()->get("msg")["reason-too-long"]));
                return;
            }

            $session->setAFK(true);
            $session->setReason($reason);
        });
        $form->setTitle(TextFormat::colorize(Main::getInstance()->getConfig()->get("forms")["title"]));
        $form->addLabel(TextFormat::colorize(Main::getInstance()->getConfig()->get("forms")["content"]));
        $inputReasonConfig = Main::getInstance()->getConfig()->get("forms")["input-reason"];
        $form->addInput(TextFormat::colorize($inputReasonConfig["text"]), TextFormat::colorize($inputReasonConfig["placeholder"]), TextFormat::colorize($inputReasonConfig["default"]));
        $player->sendForm($form);
    }
}