<?php
namespace Geeksdevelop\Cart\Main;

use Illuminate\Support\Collection;

class Item 
{
    /**
     * The ID of the cart item.
     *
     * @var int|string
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var int|float
     */
    public $qty;

    /**
     * The name of the cart item.
     *
     * @var string
     */
    public $name;

    /**
     * The price of the cart item.
     *
     * @var float
     */
    public $price;

    /**
     * The attributes for this cart item.
     *
     * @var array
     */
    public $attributes = null;

    public function __construct(Array $items = []) 
    {
        //$this->validateArgument($items);

        foreach ($items as $key => $item) {
            if ($key == 'price')
                $this->price = floatval($item);
            elseif ($key == 'attributes'){

                foreach ($item as $key => $value) 
                    $data[$key] = new Collection($value);

                $this->attributes = $data;
            }else
                $this->$key = $item;
        }
    }

    protected function validateArgument(Array $data)
    {
        /*
        foreach ($data as $key => $value) {
            if ($key == 'id' && empty($value['id']))
                throw new \InvalidArgumentException('Please supply a valid identifier.');
            elseif ($key == 'name' && empty($value['name']))
                throw new \InvalidArgumentException('Please supply a valid name.');
            else ($key == 'price' && strlen($value['price']) < 0 || ! is_numeric($value['price']))
                throw new \InvalidArgumentException('Please supply a valid price.');
        }
        */
    }

    /**
     * Get an attribute from the cart item or get the associated model.
     *
     * @param string $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if(property_exists($this, $attribute)) {
            return $this->{$attribute};
        }
    }

    public function subtotal()
    {
        $price = $this->price;
        if ($this->attributes) {
            foreach ($this->attributes as $attribute) 
                if ($attribute->get('price'))
                    $price += $attribute->get('price');
        }

        return $price * $this->qty;
    }

    public function total()
    {
        return $this->subtotal();
    }
}