<?php
abstract class BasicEnum {
    private static $constCacheArray = NULL;

    private static function getConstants() {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true) {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}

abstract class errors extends BasicEnum
{
    const noError = 0;
    const unknown = 4;
    const noSuchEvent = 1;
	const closedService = 2;
    const noSuchService = 3;
	const noSuchOwner = 5;
    const titleNotPermited = 6;
    const wrongScheduelSyntax = 7;
    const wrongAdditionalInfoSyntax = 8;
    const noSuchTurn = 9;
    const noMoreTurns = 10;
    const noSuchCommand = 11;
    const serviceAlreadyExists =12;
}
