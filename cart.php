<?php
class CartItem {
    public float $price {
        set => $value >= 0 ? $value : throw new InvalidArgumentException;
    }
    public int $count {
        set => $value > 0 ? $value : throw new InvalidArgumentException;
    }

    function __construct(int $count, float $price) {
        $this->count = $count;
        $this->price = $price;
    }
}

class Cart {
    private array $items = [];
    private const float SHIPPING_PRICE = 99.00; // CZK

    public function addItem(string $name, int $count, float $price) : void {
        $this->items[$name] = new CartItem($count, $price);
    }

    public function setItemCount(string $name, int $newCount) : void {
        $item = $this->items[$name] ?? throw new InvalidArgumentException("No item with name " . $name . " in cart");

        if ($newCount == 0)
            unset($this->items[$name]);
        else
            $item->count = $newCount;
    }

    public function computePrice(string $code = "") : float {
        $total = array_reduce($this->items, fn($acc, $item) => $acc + $item->count * $item->price) + self::SHIPPING_PRICE;
        $total = match($code) {
            "LETO10" => $total * 0.9,
            "STOVKA" => $total > 1000 ? $total - 100 : $total,
            "DOPRAVA" => $total - self::SHIPPING_PRICE,
            default => $total
        };

        return round($total, 2); // round to valid price in CZK
    }
}

// usage
$cart = new Cart;
$cart->addItem("Shoes", 1, 800);

echo "test LETO10: " . $cart->computePrice("LETO10") . PHP_EOL;
echo "test STOVKA with < 1000: " . $cart->computePrice("STOVKA") . PHP_EOL;

$cart->addItem("Shirt", 1, 200);
$cart->setItemCount("Shirt", 2);

echo "test STOVKA with > 1000: " . $cart->computePrice("STOVKA") . PHP_EOL;

$cart->addItem("Hat", 2, 100);

echo "test DOPRAVA: " . $cart->computePrice("DOPRAVA") . PHP_EOL;
echo "test no code: " . $cart->computePrice() . PHP_EOL;

$cart->setItemCount("Hat", 0);
echo "test no code without hats: " . $cart->computePrice() . PHP_EOL;
?>
