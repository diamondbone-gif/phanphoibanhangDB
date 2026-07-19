<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicationSmokeTest extends TestCase
{
    #[Test]
    public function public_home_and_health_endpoints_are_available(): void
    {
        $this->get('/')->assertOk();
        $this->get('/up')->assertOk();
    }

    #[Test]
    #[DataProvider('protectedScreens')]
    public function protected_admin_screens_resolve_and_require_login(string $uri): void
    {
        $this->get($uri)->assertRedirect('/login');
    }

    public static function protectedScreens(): array
    {
        return [
            'dashboard' => ['/admin/dashboard'],
            'customers' => ['/admin/customers'],
            'customer care' => ['/admin/customer-care'],
            'products' => ['/admin/products'],
            'inventory' => ['/admin/inventory'],
            'orders' => ['/admin/sales/orders'],
            'commissions' => ['/admin/commissions'],
        ];
    }
}
