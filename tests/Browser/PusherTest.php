<?php
namespace Tests\Browser;

use App\User;
use Laravel\Dusk\Browser;
use PHPUnit_Framework_Assert as PHPUnit;
use Tests\Browser\Pages\Home;
use Tests\DuskTestCase;

class PusherTest extends DuskTestCase
{

    /**
     * make sure online users can see and start  playing with each other
     *
     * @return void
     */
    public function test_can_see_online_users()
    {
        $this->browse(function (Browser $first, Browser $second) {
            list($user1, $user2) = factory(User::class, 2)->create();
            $first->loginAs($user1)->visit(new Home);
            $first->assertSee('No registered players online.');

            $second->loginAs($user2)->visit(new Home);
            $second->waitForText('Players online');
            $second->assertSee($user2->name);

            $first->waitForText('Players online');
            $first->assertSee($user1->name);

            $second->click('span.glyphicon-play');
            $grid_width = 8;
            $second->whenAvailable('#modal-game-request', function ($modal) use($grid_width) {
                $modal->select('#grid_width', $grid_width);
                $modal->press('Send');
            });

            $first->waitForText($user2->name . " wants to play with you on a $grid_width x $grid_width grid!");
            $first->press('Accept');

            $second->waitForText("You're playing against {$user1->name}");
            $second->assertSee($user2->name . "'s turn");

            $first->waitForText("You're playing against {$user2->name}");
            PHPUnit::assertCount(16, $first->elements('div.cell.pointer'), 'Unexpected grid width found.');
        });
    }
}