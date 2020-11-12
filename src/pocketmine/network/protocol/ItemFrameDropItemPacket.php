<?php

namespace pocketmine\network\protocol;

class ItemFrameDropItemPacket extends PEPacket{

	const NETWORK_ID = Info::ITEM_FRAME_DROP_ITEM_PACKET;
	const PACKET_NAME = "ITEM_FRAME_DROP_ITEM_PACKET";

	public $x;
	public $y;
	public $z;

	public function decode($playerProtocol){
	    $this->z = $this->getVarInt();
	    $this->x = $this->getSignedVarInt();
	    $this->y = $this->getVarInt();
	}

	public function encode($playerProtocol){
	}
	
}
