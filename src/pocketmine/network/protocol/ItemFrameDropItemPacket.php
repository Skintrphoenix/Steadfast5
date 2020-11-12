<?php

namespace pocketmine\network\protocol;

class ItemFrameDropItemPacket extends PEPacket{

	const NETWORK_ID = Info::ITEM_FRAME_DROP_ITEM_PACKET;
	const PACKET_NAME = "ITEM_FRAME_DROP_ITEM_PACKET";

	public $x;
	public $y;
	public $z;

	public function decode($playerProtocol){
<<<<<<< HEAD
<<<<<<< HEAD
	    $this->z = $this->getVarInt();
	    $this->x = $this->getSignedVarInt();
	    $this->y = $this->getVarInt();
=======
	    $this->x = $this->readCoord();
	    $this->y = $this->readCoord();
	    $this->z = $this->readCoord();
=======
	    $this->x = $this->getSignedVarInt();
	    $this->y = $this->getVarInt();
	    $this->z = $this->getSignedVarInt();
>>>>>>> 89d8d235... Partially fixed Item Frame bug where frames used to not drop items at all
	    /*$this->z = $this->getVarInt();
	    $this->x = $this->getSignedVarInt();
	    $this->y = $this->getVarInt();*/
>>>>>>> 3a7458cf... persona and custom geo corrections
	}

	public function encode($playerProtocol){
<<<<<<< HEAD
	}
	
<<<<<<< HEAD
=======
	private function readCoord(){
	    $n = (int) $this->parse();
	    return ($n >> 1) ^ -($n & 1);
	}
	
	private function parse(){
	    $result = 0;
	    for ($shift = 0; $shift < 64; $shift += 7){
	        $b = $this->getByte();
	        $result |= ($b & 0x7F) << $shift;
	        if (($b & 0x80) == 0) {
	            return $result;
	        }
	    }
	    throw new \InvalidArgumentException("Varint is too big!");
=======
	    $this->putSignedVarInt($this->x);
	    $this->putVarInt($this->y);
	    $this->putSignedVarInt($this->z);
>>>>>>> 89d8d235... Partially fixed Item Frame bug where frames used to not drop items at all
	}
>>>>>>> 3a7458cf... persona and custom geo corrections
}
