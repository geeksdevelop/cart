<?php 

namespace Geeksdevelop\Cart\Main;

use Illuminate\Support\Collection;

class Condition
{
    /**
     * The ID of the cart item.
     *
     * @var string
     */
    public $name;
    
    /**
     * The quantity for this cart item.
     *
     * @var string
     */
    public $type;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $target;

    /**
     * The price of the cart item.
     *
     * @var float
     */
    public $value;

    public function __construct(Array $items) {
        foreach ($items as $key => $item) {
            $this->$key = $item;
        }
    }

    public function getValue()
    {
        preg_match("/%/", $this->value, $array);

        if (count($array) != 0)
            return floatval($this->value) / 100;
        else
            return $this->value;
    }

    public function calculator($sudtotal)
    {
        preg_match("/%/", $this->value, $array);
        if (count($array) != 0)
            return $sudtotal * (floatval($this->value) / 100);
        else
            return $this->value;
    }
    

} 