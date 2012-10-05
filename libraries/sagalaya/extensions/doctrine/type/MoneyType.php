<?php

namespace sagalaya\extensions\doctrine\type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use sagalaya\extensions\util\Number;

class MoneyType extends Type {

	const MONEY = 'money';

	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return 'Decimal(20,2)';
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return new Number($value);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return "$value";
	}

	public function getName()
	{
		return self::MONEY;
	}
}