<?php

namespace Tests\Unit;

use App\Http\Middleware\BlockDemoAgenceMutations;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class BlockDemoAgenceMutationsTest extends TestCase
{
    public function test_demo_agency_can_consult_pages(): void
    {
        $request = Request::create('/agence/proprietes', 'GET');
        $response = (new BlockDemoAgenceMutations())->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_demo_agency_cannot_mutate_data_through_json(): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->once()->andReturn((object) [
            'agence' => (object) ['statut' => 'en_demo'],
        ]);
        Auth::shouldReceive('guard')->with('user')->andReturn($guard);

        $request = Request::create('/agence/proprietes', 'POST', server: ['HTTP_ACCEPT' => 'application/json']);
        $response = (new BlockDemoAgenceMutations())->handle($request, fn () => response('mutation exécutée'));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(BlockDemoAgenceMutations::MESSAGE, $response->getData(true)['message']);
    }

    public function test_active_agency_can_mutate_data(): void
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->once()->andReturn((object) [
            'agence' => (object) ['statut' => 'active'],
        ]);
        Auth::shouldReceive('guard')->with('user')->andReturn($guard);

        $request = Request::create('/agence/proprietes', 'POST');
        $response = (new BlockDemoAgenceMutations())->handle($request, fn () => response('ok'));

        $this->assertSame(200, $response->getStatusCode());
    }
}
