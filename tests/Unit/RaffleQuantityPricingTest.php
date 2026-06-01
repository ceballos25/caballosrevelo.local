<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Application\Pricing\RaffleQuantityPricing;
use PHPUnit\Framework\TestCase;

final class RaffleQuantityPricingTest extends TestCase
{
    private function pricing(): RaffleQuantityPricing
    {
        return RaffleQuantityPricing::forTest(true, 65000, 60000, 55000);
    }

    public function testFirstNumberCosts65000(): void
    {
        $result = $this->pricing()->calculate(1);

        self::assertSame(65000, $result['total']);
        self::assertFalse($result['promo_active']);
    }

    public function testTwoNumbersCost120000At60000Each(): void
    {
        $result = $this->pricing()->calculate(2);

        self::assertSame(120000, $result['total']);
        self::assertTrue($result['promo_active']);
        self::assertTrue($result['pair_promo_active']);
        self::assertSame(2, $result['second_count']);
    }

    public function testThreeNumbers(): void
    {
        $result = $this->pricing()->calculate(3);

        self::assertSame(165000, $result['total']);
        self::assertSame(3, $result['third_plus_count']);
        self::assertSame(0, $result['second_count']);
        self::assertTrue($result['promo_active']);
        self::assertFalse($result['pair_promo_active']);
    }

    public function testFourNumbers(): void
    {
        $result = $this->pricing()->calculate(4);

        self::assertSame(220000, $result['total']);
        self::assertSame(4, $result['third_plus_count']);
    }

    public function testDisabledUsesFallbackUnit(): void
    {
        $pricing = RaffleQuantityPricing::forTest(false, 65000, 60000, 55000);
        $result = $pricing->calculate(3, 50000.0);

        self::assertSame(150000, $result['total']);
        self::assertFalse($result['promo_active']);
    }
}
