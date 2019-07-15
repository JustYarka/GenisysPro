<?php namespace pocketmine\utils;
use pocketmine\item\Item;
class BinaryStream extends \stdClass {
	public $offset;
	public $buffer;
	public function __construct($buffer = "", $offset = 0){
		$this->buffer = $buffer;
		$this->offset = $offset;
	}
	public function reset(){
		$this->buffer = "";
		$this->offset = 0;
	}
	public function setBuffer($buffer = null, $offset = 0){
		$this->buffer = $buffer;
		$this->offset = (int) $offset;
	}
	public function getOffset(){
		return $this->offset;
	}
	public function getBuffer(){
		return $this->buffer;
	}
	public function getRemaining(){
        $str = substr($this->buffer, $this->offset);
        $this->offset = strlen($this->buffer);
        return $str;
    }
	public function get($len){
		if($len < 0){
			$this->offset = strlen($this->buffer) - 1;
			return "";
		}elseif($len === true){
			$str = substr($this->buffer, $this->offset);
			$this->offset = strlen($this->buffer);
			return $str;
		}
                
		$rem = strlen($this->buffer) - $this->offset;
		if($rem < $len){
			throw new \ErrorException("Запрошено слишком много байтов"); // Thank you, YarkaDev
		}
		
		if(!isset($this->buffer{$this->offset})){
			throw new \ErrorException("Выход за границы буфера");
		}
		return $len === 1 ? $this->buffer{$this->offset++} : substr($this->buffer, ($this->offset += $len) - $len, $len);
	}
	public function put($str){
		$this->buffer .= $str;
	}
	public function getBool() : bool{
		return (bool) $this->getByte();
	}
	public function putBool($v){
		$this->putByte((bool) $v);
	}
	public function getLong(){
		return Binary::readLong($this->get(8));
	}
	public function putLong($v){
		$this->buffer .= Binary::writeLong($v);
	}
	public function getInt(){
		return Binary::readInt($this->get(4));
	}
	public function putInt($v){
		$this->buffer .= Binary::writeInt($v);
	}
	public function getLLong(){
		return Binary::readLLong($this->get(8));
	}
	public function putLLong($v){
		$this->buffer .= Binary::writeLLong($v);
	}
	public function getLInt(){
		return Binary::readLInt($this->get(4));
	}
	public function putLInt($v){
		$this->buffer .= Binary::writeLInt($v);
	}
	public function getSignedShort(){
		return Binary::readSignedShort($this->get(2));
	}
	public function putShort($v){
		$this->buffer .= Binary::writeShort($v);
	}
	public function getShort(){
		return Binary::readShort($this->get(2));
	}
	public function putSignedShort($v){
		$this->buffer .= Binary::writeShort($v);
	}
	public function getFloat(int $accuracy = -1){
		return Binary::readFloat($this->get(4), $accuracy);
	}
	public function putFloat($v){
		$this->buffer .= Binary::writeFloat($v);
	}
	public function getLShort($signed = true){
		return $signed ? Binary::readSignedLShort($this->get(2)) : Binary::readLShort($this->get(2));
	}
	public function putLShort($v){
		$this->buffer .= Binary::writeLShort($v);
	}
	public function getLFloat(int $accuracy = -1){
		return Binary::readLFloat($this->get(4), $accuracy);
	}
	public function putLFloat($v){
		$this->buffer .= Binary::writeLFloat($v);
	}
	public function getTriad(){
		return Binary::readTriad($this->get(3));
	}
	public function putTriad($v){
		$this->buffer .= Binary::writeTriad($v);
	}
	public function getLTriad(){
		return Binary::readLTriad($this->get(3));
	}
	public function putLTriad($v){
		$this->buffer .= Binary::writeLTriad($v);
	}
	public function getByte(){
		if ($this->offset >= strlen($this->buffer))
			return;
		return ord($this->buffer{$this->offset++});
	}
	public function putByte($v){
		$this->buffer .= chr($v);
	}
	public function getUUID(){
		return UUID::fromBinary($this->get(16));
	}
	public function putUUID(UUID $uuid){
		$this->put($uuid->toBinary());
	}
	public function getSlot(){
		$id = $this->getVarInt();
		if($id <= 0){
			return Item::get(0, 0, 0);
		}
		$auxValue = $this->getVarInt();
		$data = $auxValue >> 8;
		if($data === 0x7fff){
			$data = -1;
		}
		$cnt = $auxValue & 0xff;
		$nbtLen = $this->getLShort();
		$nbt = "";
		if($nbtLen > 0){
			$nbt = $this->get($nbtLen);
		}
		$canPlaceOn = $this->getVarInt();
		if($canPlaceOn > 0){
			for($i = 0; $i < $canPlaceOn; ++$i){
				$this->getString();
			}
		}
		$canDestroy = $this->getVarInt();
		if($canDestroy > 0){
			for($i = 0; $i < $canDestroy; ++$i){
				$this->getString();
			}
		}
		return Item::get($id, $data, $cnt, $nbt);
	}
	public function putSlot(Item $item){
		if($item->getId() === 0){
			$this->putVarInt(0);
			return;
		}
		$this->putVarInt($item->getId());
		$auxValue = (($item->getDamage() & 0x7fff) << 8) | $item->getCount();
		$this->putVarInt($auxValue);
		$nbt = $item->getCompoundTag();
		$this->putLShort(strlen($nbt));
		$this->put($nbt);
		$this->putVarInt(0);
		$this->putVarInt(0);
	}
	public function getString(){
		return $this->get($this->getUnsignedVarInt());
	}
	public function putString($v){
		$this->putUnsignedVarInt(strlen($v));
		$this->put($v);
	}
	public function getUnsignedVarInt(){
		return Binary::readUnsignedVarInt($this);
	}
	public function putUnsignedVarInt($v){
		$this->put(Binary::writeUnsignedVarInt($v));
	}
	public function getVarInt(){
		return Binary::readVarInt($this);
	}
	public function putVarInt($v){
		$this->put(Binary::writeVarInt($v));
	}
	public function getEntityId(){
		return $this->getVarInt();
	}
	public function putEntityId($v){
		$this->putVarInt($v);
	}
	public function getBlockCoords(&$x, &$y, &$z){
		$x = $this->getVarInt();
		$y = $this->getUnsignedVarInt();
		$z = $this->getVarInt();
	}
	public function putBlockCoords($x, $y, $z){
		$this->putVarInt($x);
		$this->putUnsignedVarInt($y);
		$this->putVarInt($z);
	}
	public function getVector3f(&$x, &$y, &$z){
		$x = $this->getLFloat(4);
		$y = $this->getLFloat(4);
		$z = $this->getLFloat(4);
	}
	public function putVector3f($x, $y, $z){
		$this->putLFloat($x);
		$this->putLFloat($y);
		$this->putLFloat($z);
	}
	public function feof(){
		return !isset($this->buffer{$this->offset});
	}
}