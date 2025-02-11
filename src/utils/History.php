<?php

namespace xeonch\afksystem\utils;

use DateTime;
use Exception;
use pocketmine\player\Player;
use xeonch\afksystem\AfkSystem as Main;

class History
{

    private string $logFile;
    private array $config;

    public function __construct()
    {
        $main = Main::getInstance();
        $this->logFile = $main->getDataFolder() . "history.log";
        $this->config = $main->getConfig()->get("history");

        if (!file_exists($this->logFile)) {
            touch($this->logFile);
        }
        
        if ($this->config["enable"] && $this->config["auto-delete-days"] > 0) {
            $this->deleteOldHistory($this->config["auto-delete-days"]);
        }
    }

    /**
     * Add new AFK history entry
     */
    public function addHistory(Player $player, string $reason, string $duration): bool
    {
        if (!$this->config["enable"]) {
            return false;
        }

        try {
            $timestamp = new DateTime();
            $entry = str_replace(
                    ["{DATE}", "{PLAYER}", "{REASON}", "{DURATION}"],
                    [
                        $timestamp->format($this->config["date-format"]),
                        $player->getName(),
                        $reason,
                        $duration
                    ],
                    $this->config["format"]
                ) . "\n";

            return file_put_contents($this->logFile, $entry, FILE_APPEND) !== false;
        } catch (Exception $e) {
            Main::getInstance()->getLogger()->error("Failed to add history: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get AFK history for a specific player
     */
    public function getHistory(string $playerName, int $limit = 0): array
    {
        if (!$this->config["enable"]) {
            return [];
        }

        try {
            if (!file_exists($this->logFile)) {
                return [];
            }

            $history = [];
            $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                return [];
            }

            $lines = array_reverse($lines); // Most recent first

            foreach ($lines as $line) {
                if (stripos($line, "Player: " . $playerName) !== false) {
                    $history[] = $line;

                    if ($limit > 0 && count($history) >= $limit) {
                        break;
                    }
                }
            }

            return $history;
        } catch (Exception $e) {
            Main::getInstance()->getLogger()->error("Failed to get history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete history entries older than specified days
     */
    public function deleteOldHistory(int $days): bool
    {
        if (!$this->config["enable"]) {
            return false;
        }

        try {
            if (!file_exists($this->logFile)) {
                return true;
            }

            $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                return false;
            }

            $cutoffDate = new DateTime("-$days days");
            $newLines = [];

            foreach ($lines as $line) {
                if (preg_match('/^\[([\d\- :]+)]/', $line, $matches)) {
                    $entryDate = DateTime::createFromFormat($this->config["date-format"], $matches[1]);
                    if ($entryDate && $entryDate > $cutoffDate) {
                        $newLines[] = $line;
                    }
                }
            }
            return file_put_contents($this->logFile, implode("\n", $newLines) . "\n") !== false;
        } catch (Exception $e) {
            Main::getInstance()->getLogger()->error("Failed to delete history: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all history
     */
    public function clearHistory(): bool
    {
        if (!$this->config["enable"]) {
            return false;
        }

        try {
            return file_put_contents($this->logFile, '') !== false;
        } catch (Exception $e) {
            Main::getInstance()->getLogger()->error("Failed to clear history: " . $e->getMessage());
            return false;
        }
    }
}