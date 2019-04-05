<?php

namespace GuiShop;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\{Item, ItemBlock};
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat as TF;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\event\server\DataPacketReceiveEvent;
use GuiShop\Modals\elements\{Dropdown, Input, Button, Label, Slider, StepSlider, Toggle};
use GuiShop\Modals\network\{GuiDataPickItemPacket, ModalFormRequestPacket, ModalFormResponsePacket, ServerSettingsRequestPacket, ServerSettingsResponsePacket};
use GuiShop\Modals\windows\{CustomForm, ModalWindow, SimpleForm};
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender, CommandExecutor};

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener {
  public $shop;
  public $item;

  //documentation for setting up the items
  /*
  "Item name" => [item_id, item_damage, buy_price, sell_price]
  */
public $Blocks = [
    "ICON" => ["Blocks",2,0],
    "Oak Wood" => [17,0,30,15],
    "Birch Wood" => [17,2,30,15],
    "Spruce Wood" => [17,1,30,15],
    "Dark Oak Wood" => [162,1,30,15],
	"Cobblestone" => [4,0,10,5],
	"Obsidian" => [49,0,500,25],
	"Bedrock" => [7,0,150000,500],
	"Sand " => [12,0,15,7],
    "Sandstone " => [24,0,15,7],
	"Nether Rack" => [87,0,15,7],
    "Glass" => [20,0,50,25],
    "Glowstone" => [89,0,100,50],
    "Sea Lantern" => [169,0,100,50],
    "Grass" => [2,0,20,10],      
    "Dirt" => [3,0,10, 5],
    "Stone" => [1,0,20,10],
    "Planks" => [5,0,20,10],
    "Prismarine" => [168,0,30,20],
    "End Stone" => [121,0,30,20],
    "Glass" => [20,0,50,30],
    "Purpur Blocks" => [201,0,50,30],
    "Quartz Block" => [155,0,100,30],
    "Sea Lantern" => [169,0,100,30],
    "Lapis Block" => [22,0,1000,50],
    "White Wool" => [35,0,100,20],
    "Stone Slab" => [44,0,100,20],
    "Stone Stairs" => [67,0,100,20],
    "Snow" => [80,0,500,50],
    "Stone Bricks" => [98,0,500,50],
    "White Stained Glass" => [160,0,500,50],
    "Orange Stained Glass" => [160,1,1000,10],
    "Magenta Stained Glass" => [160,2,1000,10],
    "Light Blue Stained Glass" => [160,3,1000,10],
    "Yellow Stained Glass" => [160,4,1000,10],
    "Lime Stained Glass" => [160,5,1000,10],
    "Slime Blocks" => [165,0,5000,50]
  ];

  public $Ores = [
    "ICON" => ["Ores",266,0],
    "Coal" => [263,0,1000,10],
    "Iron Ingot" => [265,0,2000,50],
    "Gold Ingot" => [266,0,3000,2],
    "Diamond" => [264,0,5000,3],
    "Lapis" => [351,4,5000,20]
  ];

  public $Tools = [
    "ICON" => ["Tools",278,0],
    "Diamond Pickaxe" => [278,0,500,250],
    "Diamond Shovel" => [277,0,500,250],
    "Diamond Axe" => [279,0,500,250],
    "Diamond Hoe" => [293,0,500,250],
    "Diamond Sword" => [276,0,750,375],
    "Iron Pickaxe" => [257,0,500,0],
    "Iron Shovel" => [256,0,500,0],
    "Iron Axe" => [258,0,500,0],
    "Iron Hoe" => [292,0,500,0],
    "Iron Sword" => [267,0,500,0],	  
    "Bow" => [261,0,400,200],
    "Arrow" => [262,0,25,5]
  ];

  public $Armor = [
    "ICON" => ["Armor",311,0],
    "Diamond Helmet" => [310,0,1000,0],
    "Diamond Chestplate" => [311,0,2500,0],
    "Diamond Leggings" => [312,0,1500,0],
    "Diamond Boots" => [313,0,1000,0],
    "Iron Helmet" => [306,0,750,0],
    "Iron Chestplate" => [307,0,750,0],
    "Iron Leggings" => [308,0,750,0],	
    "Iron Boots" => [309,0,750,0]  
	  
  ];

  public $Farming = [
    "ICON" => ["Farming",293,0],
    "Pumpkin" => [86,0,50,25],
    "Melon" => [360,13,50,25],
    "Carrot" => [391,0,80,20],
    "Potato" => [392,0,80,20],
    "Sugarcane" => [338,0,80,10],
    "Wheat" => [296,6,80,40],
    "Pumpkin Seed" => [361,0,20,10],
    "Melon Seed" => [362,0,20,10],
    "Seed" => [295,0,20,10]
  ];

  public $Miscellaneous = [
    "ICON" => ["Miscellaneous",368,0],
    "Steak" => [364,0,15,0],	         
    "Cooked Chicken" => [366,0,15,0],	  
    "Golden Apple" => [322,0,1000,100],  
    "Furnace" => [61,0,20,10],
    "Crafting Table" => [58,0,20,10],
    "Ender Chest " => [130,0,1000,50],
    "Enderpearl" => [368,0,1000,100],
    "Bone" => [352,0,50,25],
    "Book & Quill" => [386,0,100,0],
    "Boats" => [333,0,1000,10],
    "Brewing Stand" => [117,0,500,20],
    "Carpet" => [171,0,100,5],
    "White Bed" => [355,0,100,10],
    "Orange Bed" => [355,1,200,20],
    "Magenta Bed" => [355,2,200,20],
    "Light Blue Bed" => [355,3,200,20],
    "Yellow Bed" => [355,4,200,20],
    "Lime Bed" => [355,5,200,20],
    "Anvil" => [145,0,500,50]
  ];

  public $Raiding = [
    "ICON" => ["Raiding",46,0],
    "Flint & Steel" => [259,0,100,50],
    "Torch" => [50,0,5,2],
    "Packed Ice " => [174,0,500,250],
    "Water" => [9,0,50,10],
    "Lava" => [10,0,50,10],
    "Redstone" => [331,0,50,25],
    "Chest" => [54,0,100,50],
    "TNT" => [46,0,10000,50]
  ];
	
  public $Mobs = [
    "ICON" => ["Mobs",52,0],
    "Chicken" => [52,10,10000,5000],
    "Cow" => [52,11,20000,5000],
    "Sheep" => [52,13,30000,5000],
    "Skeleton" => [52,34,40000,5000],
    "Zombie" => [52,32,55000,5000],
    "Blaze" => [52,43,500000,5000],
    "Iron Golem" => [52,20,100000,5000],
    "Zombie Pigman" => [52,36,1000000,5000]
  ];

  public $Potions = [
    "ICON" => ["Potions",373,0],
    "Strength" => [373,33,1000,100],
    "Regeneration" => [373,28,1000,100],
    "Speed" => [373,16,1000,500],
    "Fire Resistance" => [373,13,1000,100],
    "Poison (SPLASH)" => [438,27,1000,100],
    "Healing II (SPLASH)" => [438,22,1000,100],
    "Weakness (SPLASH)" => [438,35,1000,100],
    "Slowness (SPLASH)" => [438,17,1000,100]
  ];

  public $Skulls = [
    "ICON" => ["Heads",397,0],
    "Zombie Skull" => [397,0,500,50],
    "Wither Skull" => [397,0,500,50],
    "Skin Head" => [397,0,50,10],
    "Creeper Skull" => [397,0,500,50],
    "Dragon Skull" => [397,0,1000,60],
    "Skeleton Skull" => [397,0,500,50]
  ];
	
  public $MobDrop = [
    "ICON" => ["MobDrop",369,0],
    "Blaze Rod" => [369,0,500,50],
    "Gold Nuggets" => [371,0,500,50],
    "Rotten Flesh" => [367,0,500,25],
    "GunPowder" => [289,0,500,50]
  ];	

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    PacketPool::registerPacket(new GuiDataPickItemPacket());
		PacketPool::registerPacket(new ModalFormRequestPacket());
		PacketPool::registerPacket(new ModalFormResponsePacket());
		PacketPool::registerPacket(new ServerSettingsRequestPacket());
		PacketPool::registerPacket(new ServerSettingsResponsePacket());
    $this->item = [$this->MobDrop, $this->Skulls, $this->Potions, $this->Mobs, $this->Raiding, $this->Farming, $this->Armor, $this->Tools, $this->Ores, $this->Blocks, $this->Miscellaneous];
  }

  public function sendMainShop(Player $player){
    $ui = new SimpleForm("§8< §eMetro §7PvP §8>","       §3Purchase and Sell items Here!");
    foreach($this->item as $category){
      if(isset($category["ICON"])){
        $rawitemdata = $category["ICON"];
        $button = new Button($rawitemdata[0]);
        $button->addImage('url', "http://avengetech.me/items/".$rawitemdata[1]."-".$rawitemdata[2].".png");
        $ui->addButton($button);
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 110;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendShop(Player $player, $id){
    $ui = new SimpleForm("§8< §eMetro §7PvP §8>","       §3Purchase and Sell items Here!");
    $ids = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $id){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            $button = new Button($name);
            $button->addImage('url', "http://avengetech.me/items/".$item[0]."-".$item[1].".png");
            $ui->addButton($button);
          }
        }
      }
    }
    $pk = new ModalFormRequestPacket();
    $pk->formId = 111;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sendConfirm(Player $player, $id){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $id){
              $this->item[$player->getName()] = $id;
              $iname = $name;
              $cost = $item[2];
              $sell = $item[3];
              break;
            }
          }
          $idi++;
        }
      }
    }

    $ui = new CustomForm($iname);
    $slider = new Slider("§dAmount ",1,500,0);
    $toggle = new Toggle("§5Selling");
    if($sell == 0) $sell = "0";
    $label = new Label(TF::GREEN."Buy: $".TF::GREEN.$cost.TF::RED."\nSell: $".TF::RED.$sell);
    $ui->addElement($label);
    $ui->addElement($toggle);
    $ui->addElement($slider);
    $pk = new ModalFormRequestPacket();
    $pk->formId = 112;
    $pk->formData = json_encode($ui);
    $player->dataPacket($pk);
    return true;
  }

  public function sell(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if($sell == 0){
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§cThis is not sellable!");
                return true;
              }
              if($player->getInventory()->contains(Item::get($id,$damage,$amount))){
                $player->getInventory()->removeItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->addMoney($player, $sell);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§bYou have sold §3$amount $iname §bfor §3$$sell");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§2You do not have §5$amount $iname!");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function purchase(Player $player, $data, $amount){
    $ids = -1;
    $idi = -1;
    foreach($this->item as $category){
      $ids++;
      $rawitemdata = $category["ICON"];
      if($ids == $this->shop[$player->getName()]){
        $name = $rawitemdata[0];
        $data = $this->$name;
        foreach($data as $name => $item){
          if($name != "ICON"){
            if($idi == $this->item[$player->getName()]){
              $iname = $name;
              $id = $item[0];
              $damage = $item[1];
              $cost = $item[2]*$amount;
              $sell = $item[3]*$amount;
              if(EconomyAPI::getInstance()->myMoney($player) > $cost){
                $player->getInventory()->addItem(Item::get($id,$damage,$amount));
                EconomyAPI::getInstance()->reduceMoney($player, $cost);
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::GREEN . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§bYou purchased §3$amount $iname §bfor §3$$cost");
              }else{
                $player->sendMessage(TF::BOLD . TF::DARK_GRAY . "(" . TF::RED . "!" . TF::DARK_GRAY . ") " . TF::RESET . TF::GRAY . "§2You do not have enough money to buy §5$amount $iname");
              }
              unset($this->item[$player->getName()]);
              unset($this->shop[$player->getName()]);
              return true;
            }
          }
          $idi++;
        }
      }
    }
    return true;
  }

  public function DataPacketReceiveEvent(DataPacketReceiveEvent $event){
    $packet = $event->getPacket();
    $player = $event->getPlayer();
    if($packet instanceof ModalFormResponsePacket){
      $id = $packet->formId;
      $data = $packet->formData;
      $data = json_decode($data);
      if($data === Null) return true;
      if($id === 110){
        $this->shop[$player->getName()] = $data;
        $this->sendShop($player, $data);
        return true;
      }
      if($id === 111){
        //$this->shop[$player->getName()] = $data;
        $this->sendConfirm($player, $data);
        return true;
      }
      if($id === 112){
        $selling = $data[1];
        $amount = $data[2];
        if($selling){
          $this->sell($player, $data, $amount);
          return true;
        }
        $this->purchase($player, $data, $amount);
        return true;
      }
    }
    return true;
  }

  public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
    switch(strtolower($command)){
      case "shop":
        $this->sendMainShop($player);
        return true;
    }
  }

}
