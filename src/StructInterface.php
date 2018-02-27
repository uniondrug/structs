<?php
/**
 * StructInterface.php
 *
 */

namespace Uniondrug\Structs;

interface StructInterface
{
    public static function factory($data = null);
    public function init($data);

    public function toArray();
    public function toJson($options = 0, $depth = 512);

    public function set($name, $value);
    public function setProperty($name, $value);

    public function get($name);
    public function getProperty($name);

    public function has($name);
    public function hasProperty($name);

    public function getProperties();
}
