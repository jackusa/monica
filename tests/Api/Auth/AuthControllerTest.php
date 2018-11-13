<?php

namespace Tests\Api\Auth;

use Tests\ApiTestCase;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;

class AuthControllerTest extends ApiTestCase
{
    public function setUp()
    {
        parent::setUp();
        $connection = DB::connection();

        if ($connection->getName() != 'testing') {
            $this->markTestSkipped("Set DB_CONNECTION on 'testing' to run this test.");
        }
    }

    protected $jsonStructureOAuthLogin = [
        'access_token',
        'expires_in',
    ];

    public function test_oauth_login()
    {
        $client = (new ClientRepository())->createPasswordGrantClient(
            null, config('app.name'), 'http://localhost'
        );
        $x = (new ClientRepository())->find($client->id);
        config(['monica.mobile_client_id' => $client->id]);
        config(['monica.mobile_client_secret' => $client->secret]);

        $user = factory(User::class)->create([
            'password' => bcrypt('password')
        ]);

        $response = $this->json('POST', '/oauth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure($this->jsonStructureOAuthLogin);
    }
}
