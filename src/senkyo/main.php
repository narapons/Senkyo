<?php

namespace senkyo;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;

class main extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->senkyo = new Config($this->getDataFolder() . "senkyo.yml", Config::YAML);
        $this->player = new Config($this->getDataFolder() . "player.yml", Config::YAML);
        $this->setting = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case"senkyo":
                if (!isset($args[0])) {
                    if ($sender->isOp()) {
                        $sender->sendMessage("§e[選挙システム] /senkyo [ add / remove / list / vote / setting ]");
                    } else {
                        $sender->sendMessage("§e[選挙システム] /senkyo list で政党の一覧と投票数を確認できます。");
                        $sender->sendMessage("§e[選挙システム] /senkyo vote [政党名] で指定した政党に投票できます。");
                    }
                } else {
                    switch ($args[0]) {
                        case"add":
                            if (!$sender->isOp()) {
                                $sender->sendMessage("§cこのコマンドを実行する権限がありません。");
                            } else if (!isset($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 追加をする政党名を入力してください。");
                            } else if ($this->senkyo->exists($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 「{$args[1]}」は既に追加されています。");
                            } else {
                                $this->senkyo->set($args[1], 0);
                                $this->senkyo->save();
                                $this->senkyo->reload();
                                $sender->sendMessage("§6[選挙システム] 「{$args[1]}」を追加しました。");
                            }
                            break;
                        case"remove":
                            if (!$sender->isOp()) {
                                $sender->sendMessage("§cこのコマンドを実行する権限がありません。");
                            } else if (!isset($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 削除をする政党名を入力してください。");
                            } else if (!$this->senkyo->exists($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 「{$args[1]}」は存在しません。");
                            } else {
                                $this->senkyo->remove($args[1]);
                                $this->senkyo->save();
                                $this->senkyo->reload();
                                $sender->sendMessage("§6[選挙システム] 「{$args[1]}」を削除しました。");
                            }
                            break;
                        case"list":
                            $sender->sendMessage("§6===選挙システム===");
                            foreach ($this->senkyo->getAll() as $key => $value) {
                                $sender->sendMessage("政党：{$key}   投票数{$value}");
                            }
                            break;
                        case"vote":
                            $name = $sender->getName();
                            if (!$this->setting->exists("on")) {
                                $sender->sendMessage("§e[選挙システム] 現在は選挙が行われていません。");
                            } else if (!isset($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 投票する政党を入力してください。");
                            } else if (!$this->senkyo->exists($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 「{$args[1]}」は存在しません。");
                            } else if ($this->player->exists($name)) {
                                $data = $this->player->get($name);
                                $sender->sendMessage("§e[選挙システム] 既にあなたは「{$data}」に投票をしています。");
                            } else {
                                $data = $this->senkyo->get($args[1]) + 1;
                                $this->senkyo->set($args[1], $data);
                                $this->senkyo->save();
                                $this->senkyo->reload();
                                $this->player->set($name, $args[1]);
                                $this->player->save();
                                $this->player->reload();
                                $sender->sendMessage("§6[選挙システム] 「{$args[1]}」に投票しました。");
                            }
                            break;
                        case"setting":
                            if (!$sender->isOp()) {
                                $sender->sendMessage("§cこのコマンドを実行する権限はありません。");
                            } else if (!isset($args[1])) {
                                $sender->sendMessage("§e[選挙システム] 使い方 /senkyo setting [on / off / now]");
                            } else {
                                switch ($args[1]) {
                                    case"on":
                                        if ($this->setting->exists("on")) {
                                            $sender->sendMessage("§e[選挙システム] 既に選挙は有効です。");
                                        } else {
                                            $this->setting->set("on", "on");
                                            $this->setting->save();
                                            $this->setting->reload();
                                            $sender->sendMessage("§6[選挙システム] 選挙を有効にしました。");
                                        }
                                        break;
                                    case"off":
                                        if (!$this->setting->exists("on")) {
                                            $sender->sendMessage("§e[選挙システム] 既に選挙は無効です。");
                                        } else {
                                            $this->setting->remove("on");
                                            $this->setting->save();
                                            $this->setting->reload();
                                            $sender->sendMessage("§6[選挙システム] 選挙を無効にしました。");
                                        }
                                        break;
                                    case"now":
                                        if ($this->setting->exists("on")) {
                                            $sender->sendMessage("§6[選挙システム] 選挙が有効です。");
                                        } else {
                                            $sender->sendMessage("§6[選挙システム] 選挙が無効です。");
                                        }
                                        break;
                                    default:
                                        $sender->sendMessage("§e[選挙システム] 使い方 /senkyo setting [on / off / now]");
                                        break;
                                }
                            }
                            break;
                        default:
                            if ($sender->isOp()) {
                                $sender->sendMessage("§e[選挙システム] /senkyo [ add / remove / list / vote / setting ]");
                            } else {
                                $sender->sendMessage("§e[選挙システム] /senkyo list で政党の一覧と投票数を確認できます。");
                                $sender->sendMessage("§e[選挙システム] /senkyo vote [政党名] で指定した政党に投票できます。");
                            }
                            break;
                    }
                }
        }
        return true;
    }
}
