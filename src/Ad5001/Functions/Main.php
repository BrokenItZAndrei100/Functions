<?php
  namespace Ad5001\Functions;
  use pocketmine\command\CommandSender;
  use pocketmine\command\Command;
  use pocketmine\command\ConsoleCommandSender;
  use pocketmine\event\player\PlayerCommandPreprocessEvent;
  use pocketmine\event\player\PlayerChatEvent;
  use pocketmine\command\Loader;
  use pocketmine\event\Listener;
  use pocketmine\Player;
  use pocketmine\server;
  use pocketmine\IPlayer;
  use pocketmine\utils\Config;
  use pocketmine\plugin\PluginBase;
  class Main extends PluginBase implements Listener{


    public function onEnable(){
        $this->cmds = [];
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        foreach($this->getConfig()->getAll() as $cmd => $cmds) {
            $cmd  = substr($cmd, 1); // Removing the "/"
            $this->cmds[$cmd] = new \pocketmine\command\PluginCommand($name, $this);
            $this->cmds[$cmd]->setUsage("/$cmd [arguments]");
            $this->cmds[$cmd]->setDescription("Runs function $cmd.");
            $this->cmds[$cmd]->register($this->getServer()->getCommandMap());
        }
    }



    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
      switch($command->getName()){
        case "function":
          if(isset($args[0])){
            switch($args[0]){
              case "c":
              case "create":
                if(count($args) < 2){
                  return false;
                }else{
                  $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                  $default = ["tell {sender} This is default command, modify it with /function setc <function> <Command number> <command...>","nothink", "nothink", "nothink", "nothink"];
                  $cfg->set("/".$args[1], $default);
                  $cfg->save();
                  $this->reloadConfig();
                  $this->cmds[$args[1]] = new \pocketmine\command\PluginCommand($args[1], $this);
                  $this->cmds[$args[1]]->setUsage("/{$args[1]} [arguments]");
                  $this->cmds[$args[1]]->setDescription("Runs function $args[1].");
                  $this->cmds[$args[1]]->register($this->getServer()->getCommandMap());
                  $sender->sendMessage("§4§l[Functions]§r§4 Function " . $args[1] . " has been created! You can edit it on the config or by doing /function ac <function> <command number> <command...>.");
                }
                return true;
              break;
              case "ac":
              case "addcmd":
                $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                if(is_array($cfg->get("/".$args[1]))){
                  unset($args[0]);
                  $funcname = $args[1];
                  unset($args[1]);
                  $funccmds = $cfg->get("/".$funcname);
                  array_push($funccmds, implode(" ", $args));
                  $cfg->set("/".$funcname, $funccmds);
                  $cfg->save();
                  $sender->sendMessage("§4§l[Functions]§r§4 Command ". implode(" ", $args)."  for function " . $funcname . " has been added!");
                  $this->reloadConfig();
                }else{
                  $sender->sendMessage("§4§l[Functions]§r§4 Function " . $args[1] . " not found. Create it with /function c " . $args[1]);
                }
                return true;
              break;
              case "rc":
              case "resetcmd":
                $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $func = $cfg->get("/".$args[1]);
                $oldcmd = $func[$args[2]];
                $func[$args[2]] = "nothink";
                $sender->sendMessage("§4§l[Functions]§r§4 Removed command (" . $oldcmd . ") of function " . $args[1]);
                $cfg->set("/".$args[1], $func);
                $cfg->save();
                $this->reloadConfig();
                return true;
              break;
              case "rmc":
              case "removecmd":
                $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $func = $cfg->get("/".$args[1]);
                if(!is_array($func)){
                  $sender->sendMessage("§l§4[Function]§r§4 Function $args[1] does not exist! Create it with /function create $args[1]");
                }
                unset($func[$args[2]-1]);
                $cfg->set("/".$args[1], $func);
                $cfg->save();
                $this->reloadConfig();
                $sender->sendMessage("§4§l[Functions]§r§4 Removed command $args[2] from function $args[1]");
                return true;
              break;
              case "read":
                $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $i = 1;
                $sender->sendMessage("§4§l[Functions] Commands for function " . $args[1] . ":");
                $funcname = $args[1];
                $func = $cfg->get("/".$funcname);
                foreach($func as $funccmds){
                  $sender->sendMessage("Command " . $i . ": /" . $funccmds);
                  $i += 1;
                }
                return true;
              break;
              case "delete":
              case "remove":
              case "del":
              case "rm":
                $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
                $cfg->remove($args[1]);
                $cfg->save();
                $this->reloadConfig();
                $this->cmds[$args[1]]->unregister($this->getServer()->getCommandMap());
                unset($this->cmds[$args[1]]);
                $sender->sendMessage("§4§l[Functions]§r§4 Succefully removed function " . $args[1] . ".");
                return true;
              break;
              default:
                $sender->sendMessage("§4§l[Functions]§r§4 Help for Function: \n------------------------- \n- /function create <function>:§6 Create a function \n- /function ac <function> <command id> <command>:§6 Add a command to a function \n- /function rc <function> <command id> <command>:§6 Reset a command from a function\n- /function rmc <function> <command id> <command>:§6 Remove a command from a function\n- /function read <function>:§6 Read all commands of a function \n- /function delete <function>:§6 Deletes a function \n---------------------------\n");
                return true;
              break;
            }
          }else{
            $sender->sendMessage("§4§l[Functions]§r§4 Help for Function: \n------------------------- \n- /function create <function>:§6 Create a function \n- /function ac <function> <command id> <command>:§6 Add a command to a function \n- /function rc <function> <command id> <command>:§6 Reset a command from a function\n- /function rmc <function> <command id> <command>:§6 Remove a command from a function\n- /function read <function>:§6 Read all commands of a function \n- /function delete <function>:§6 Deletes a function \n---------------------------\n");
          }
          return true;
        break;
      }
    }



    public function onCommandPreProcess(PlayerCommandPreprocessEvent $event){
        $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $args = $event->getMessage();
        $sender = $event->getPlayer();
        $args = explode(" ", $args);
        $cmds = $cfg->get($args[0]);
        if(is_array($cmds)){
            $funcname = $args[0];
            unset($args[0]);
            if($sender->isPermissionSet("func.use." . $funcname) ? $sender->hasPermission("func.use." . $funcname) : $sender->hasPermission("func.use.default")){
            foreach($cmds as $cmd){
                if($cmd !== "nothink"){
                    $cmd = str_ireplace("{sender}", $sender->getName(), $cmd);
                    $cmd = str_ireplace("{level}", $sender->getLevel()->getName(), $cmd);
                    $cmd = str_ireplace("{x}", $sender->x, $cmd);
                    $cmd = str_ireplace("{y}", $sender->y, $cmd);
                    $cmd = str_ireplace("{z}", $sender->z, $cmd);
                    $cmd = str_ireplace("{yaw}", $sender->yaw, $cmd);
                    $cmd = str_ireplace("{pitch}", $sender->pitch, $cmd);
                    if(!isset($args[1])){
                        $cmd = str_ireplace("{args[0]}", "", $cmd);
                    } else {
                      $cmd = str_ireplace("{args[0]}", $args[1], $cmd);
                    }
                    if(!isset($args[2])){
                        $cmd = str_ireplace("{args[1]}", "", $cmd);
                    } else {
                        $cmd = str_ireplace("{args[1]}", $args[2], $cmd);
                    }
                    if(!isset($args[3])){
                        $cmd = str_ireplace("{args[2]}", "", $cmd);
                    } else {
                        $cmd = str_ireplace("{args[2]}", $args[3], $cmd);
                    }
                    if(!isset($args[4])){
                        $cmd = str_ireplace("{args[3]}", "", $cmd);
                    } else {
                        $cmd = str_ireplace("{args[3]}", $args[4], $cmd);
                    }
                    $cmd = str_ireplace("{args}", json_encode($args), $cmd);
                    if($cmd === "tell " . $sender->getName() . " This is default command, modify it with /function setc <function> <Command number> <command...>"){
                        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                    }elseif(strpos($cmd, "{console}")){
                        $cmd = str_ireplace("{console}", "", $cmd);
                        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                    }elseif(strpos($cmd, "{op}")){
                        $cmd = str_ireplace("{op}", "", $cmd);
                    if ($sender->isOp()){
                        $this->getServer()->dispatchCommand($sender, $cmd);
                    } else {
                        $sender->setOp(true);
                        $this->getServer()->dispatchCommand($sender, $cmd);
                        $sender->setOp(false);
                    }
              }else{
                    $this->getServer()->dispatchCommand($sender, $cmd);
              }
            }
          }
        } else {
          $sender->sendMessage("You do not have permission to use this function.");
        }
        $event->setCancelled();
      }
    }
  }
