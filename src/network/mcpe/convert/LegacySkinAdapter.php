<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\convert;

use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function strlen;
use const JSON_THROW_ON_ERROR;

class LegacySkinAdapter implements SkinAdapter
{

	public function toSkinData(Skin $skin): SkinData
	{
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$skinData = $skin->getSkinData();
		$skinImage = match (strlen($skinData)) {
			1 * 1 * 4 => new SkinImage(1, 1, $skinData),
			64 * 32 * 4 => new SkinImage(32, 64, $skinData),
			64 * 64 * 4 => new SkinImage(64, 64, $skinData),
			128 * 128 * 4 => new SkinImage(128, 128, $skinData),
			256 * 256 * 4 => new SkinImage(256, 256, $skinData),
			default => throw new \InvalidArgumentException("Unknown skin data size " . strlen($skinData) . " bytes")
		};
		$geometryName = $skin->getGeometryName();
		if ($geometryName === "") {
			$geometryName = "geometry.humanoid.custom";
		}
		return new SkinData(
			$skin->getSkinId(),
			"", //TODO: playfab ID
			json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR),
			$skinImage,
			[],
			$capeImage,
			$skin->getGeometryData(),
			fullSkinId: $skin->getFullSkinId(),
			armSize: $skin->getArmSize(),
			skinColor: $skin->getSkinColor()
		);
	}

	public function fromSkinData(SkinData $data): Skin
	{
		$capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();

		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if (is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
			$geometryName = $resourcePatch["geometry"]["default"];
		} else {
			throw new InvalidSkinException("Missing geometry name field");
		}

		return new Skin(
			$data->getSkinId(),
			$data->getSkinImage()->getData(),
			$capeData,
			$geometryName,
			$data->getGeometryData(),
			$data->getFullSkinId(),
			$data->getArmSize(),
			$data->getSkinColor()
		);
	}
}
