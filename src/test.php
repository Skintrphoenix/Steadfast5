<?php

require_once ('./pocketmine/utils/BinaryStream.php');

function test()
{
    $b = new BinaryStream(hex2bin($a), 1);


    var_dump($b->getVarInt());
    return;
    var_dump($b->getVarInt());
    var_dump($b->getSignedVarInt());
    var_dump($b->getLFloat());
    var_dump($b->getLFloat());
    var_dump($b->getLFloat());
    var_dump($b->getLFloat());
    var_dump($b->getLFloat());
    var_dump($b->getSignedVarInt());

    var_dump($b->getShort());
    var_dump($b->getString());
    var_dump($b->getSignedVarInt());

    var_dump($b->getSignedVarInt());
    var_dump($b->getSignedVarInt());
    var_dump($b->getSignedVarInt());

    var_dump($b->getSignedVarInt());
    var_dump($b->getVarInt());
    var_dump($b->getSignedVarInt());


    var_dump($b->getByte());
    var_dump($b->getSignedVarInt());
    var_dump($b->getSignedVarInt());
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getString());

    var_dump($b->getLFloat());
    var_dump($b->getLFloat());
    var_dump($b->getByte());


    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getSignedVarInt());  // XBox Live Broadcast setting
    //	var_dump($b->getSignedVarInt()); // Platform Broadcast setting

    var_dump($b->getByte());
    var_dump($b->getByte());

    $n = ($b->getVarInt());
    var_dump($n);
    for ($i = 0; $i < $n; $i++) {
        var_dump($b->getString());
        $type = $b->getVarInt();
        switch ($type) {
            case 1:
                $b->getByte();
                break;
            case 2:
                $b->getSignedVarInt();
                break;
            case 3:
                $b->getLFloat();
                break;
        }

    }
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getSignedVarInt());
    var_dump($b->getLInt());

    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getByte());
    var_dump($b->getString());


    var_dump($b->getLInt());
    var_dump($b->getLInt());

    var_dump($b->getByte());
    var_dump($b->getByte());


    var_dump($b->getString());
    var_dump($b->getString());
    var_dump($b->getString());

    var_dump($b->getByte());
    var_dump($b->getByte());

    var_dump($b->getLong());
    var_dump($b->getSignedVarInt());
    die;
//		var_dump(bin2hex($b->get(true)));
}


test();