<?php
namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Home;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCanRegister()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/');
            $browser->assertSee('TicTacToe');
            $browser->clickLink('register');
            $browser->assertSee('Create your account');
            $browser->type('name', $name = $this->faker->name);
            $browser->type('email', $this->faker->email);
            $password = $this->faker->password;
            $browser->type('password', $password);
            $browser->type('password_confirmation', $password);
            $browser->press('Register')
                ->on(new Home)
                ->assertSee('Dashboard')
                ->assertSee($name . ', you have lost 0 times and won 0 times.');
        });
    }
}