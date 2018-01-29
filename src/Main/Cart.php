<?php 

namespace Geeksdevelop\Cart\Main;

use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;

class Cart 
{
    const INSTANCE = 'default';

    /**
     * Instance of the session manager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    private $session;

    /**
     * Holds the current cart instance.
     *
     * @var string
     */
    private $instance;

    /**
     * Cart constructor.
     *
     * @param \Illuminate\Session\SessionManager      $session
     */
    public function __construct(SessionManager $session)
    {
        $this->session = $session;
        $this->instance(self::INSTANCE);
    }

    /**
     * Set the current cart instance.
     *
     * @param string|null $instance
     * @return \Geeksdevelop\Cart\Cart
     */
    public function instance($instance = null)
    {
        $instance = $instance ?: self::INSTANCE;
        $this->instance = sprintf('%s.%s', 'cart', $instance);
        return $this;
    }

    /**
     * Get the current cart instance.
     *
     * @return string
     */
    public function currentInstance()
    {
        return str_replace('cart.', '', $this->instance);
    }

    /**
     * Add an item to the cart.
     *
     * @param array     $item
     * @return \Geeksdevelop\Cart\Item
     */
    public function add(Array $data = [])
    {
        if ($this->isMultiItems($data))
            $items = $data;
        else
            $items[] = $data;

        $content = $this->getContent();


        foreach ($items as $item) {
            $cartItem = new Item($item);
            if ($content->has($cartItem->id)) {
                $cartItem->qty += $content->get($cartItem->id)->qty;
            }
            
            $content->put($cartItem->id, $cartItem);
        }

        $this->session->put($this->instance, $content);

        if ($content->count() > 1)
            return $content;

        return $content->first();
    }

    /**
     * Update the cart item with the given id.
     *
     * @param string $id
     * @param mixed  $qty
     * @return \Geeksdevelop\Cart\Item
     */
    public function update($data, Array $options = [])
    {
        $content = $this->getContent();
        if (is_array($data))
            $items = $data;
        else
            $items[$data] = $options;
        
        foreach ($items as $key => $item) {
            $row = $this->findContent($key);
            foreach ($item as $key => $value) {
                if($key == 'qty')
                    $row->qty += $value;
                elseif($key == 'price')
                    $row->price = floatval($value);
                else
                    $row->$key = $value;
            }
            $content->put($row->id, $row);
        }

        if ($this->session->put($this->instance, $content))
            return true;
        
        return false;
    }

    /**
     * Remove the cart item with the given rowId from the cart.
     *
     * @param string $rowId
     * @return void
     */
    public function remove($data)
    {
        $content = $this->getContent();

        if (is_array($data))
            $items = $data;
        else
            $items[] = $data;

        foreach ($items as $item) {
            $row = $this->findContent($item);
            $content->pull($row->id);
        }

        $this->session->put($this->instance, $content);
    }

    /**
     * Verify if the item still exists in the cart.
     * 
     * @param string $id
     * @return boolean 
     */
    public function exists($id)
    {
        return $this->findContent($id) ? true : false;
    }

    public function conditions()
    {   
        $data = new Collection([]);

        foreach ($this->getConditions() as  $key => $condition) {
            $data->put($key, [
                'name' => $condition->name,
                'type' => $condition->type,
                'value' => $condition->calculator($this->subtotal())
            ]);
        }
        return $data;
    }

    public function setCondition(Array $data = [])
    {
        $conditions = $this->getConditions();


        if ($this->isMultiItems($data))
            $items = $data;
        else
            $items[] = $data;


        foreach ($items as $item) {
            $conditions->push(new Condition($item));
        }

        if ($this->session->put('conditions', $conditions))
            return $conditions;
    }

    public function clearCondition()
    {
        $this->session->remove('conditions');
    }
    

    /**
     * Empty the current cart instance.
     *
     * @return void
     */
    public function clear()
    {
        $this->session->remove($this->instance);
    }

    public function subtotal()
    {
        $subtotal = 0;
        foreach ($this->items() as $item) {
            $subtotal += $item->total();
        }
        return $subtotal;
    }

    public function total()
    {
        $total = $this->subtotal();

        foreach ($this->conditions() as $condition) {
            if ($condition['type'] == 'discount')
                $total -= $condition['value'];
            else
                $total += $condition['value'];
        }

        return $total;
    }

    /**
     * Get the content of the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function items()
    {
        if (is_null($this->session->get($this->instance))) {
            return new Collection([]);
        }
        return new Collection($this->session->get($this->instance));
    }

    /**
     * Get a cart item from the cart by its Id.
     *
     * @param string $id
     * @return \Geeksdevelop\Cartt\Item
     */
    private function findContent($id)
    {
        $content = $this->getContent();

        return $content->get($id) ?: null;
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getContent()
    {
        $content = $this->session->has($this->instance)
            ? $this->session->get($this->instance)
            : new Collection;
        return $content;
    }

    protected function getConditions()
    {
        $conditions = $this->session->has('conditions')
            ? $this->session->get('conditions')
            : new Collection;
        return $conditions;
    }

    /**
     * Check if the item is a multidimensional array.
     *
     * @param mixed $item
     * @return bool
     */
    private function isMultiItems($items)
    {
        return array_key_exists(0, $items);
    }
}
