<?php

namespace Geeksdevelop\Cart;

use Illuminate\Auth\Events\Logout;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('cart', 'Geeksdevelop\Cart\Main\Cart');
        $this->app->bind('condition', 'Geeksdevelop\Cart\Main\Condition');
    }
}
