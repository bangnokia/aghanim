<?php

namespace BangNokia\Aghanim\Tests\Http\Controllers;

use BangNokia\Aghanim\Http\Controllers\AghanimActionController;
use BangNokia\Aghanim\Security\ActionAuthorizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mockery;
use Orchestra\Testbench\TestCase;

class AghanimActionControllerTest extends TestCase
{
    protected $controller;
    protected $authorizer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the ActionAuthorizer
        $this->authorizer = Mockery::mock(ActionAuthorizer::class);
        
        // Create the controller with the mocked authorizer
        $this->controller = new AghanimActionController($this->authorizer);
        
        // Set up the config
        Config::set('aghanim.action_namespace', 'App\\Actions');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_404_when_action_class_does_not_exist()
    {
        // Create a request with a non-existent action
        $request = new Request();
        $request->merge([
            'action' => 'NonExistentAction',
            'params' => [],
        ]);
        
        // Set up the request headers
        $request->headers->set('X-Inertia-Component', 'Test');
        
        // Call the controller
        $response = $this->controller->handle($request);
        
        // Assert the response
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Action not found', $response->getContent());
    }

    /** @test */
    public function it_returns_403_when_action_is_not_authorized()
    {
        // Define a test action class
        if (!class_exists('App\\Actions\\TestAction')) {
            eval('namespace App\\Actions; class TestAction { public function execute() { return "test"; } }');
        }
        
        // Create a request with the test action
        $request = new Request();
        $request->merge([
            'action' => 'TestAction',
            'params' => [],
        ]);
        
        // Set up the request headers
        $request->headers->set('X-Inertia-Component', 'Test');
        
        // Set up the authorizer to deny the action
        $this->authorizer->shouldReceive('isAuthorized')
            ->once()
            ->with('App\\Actions\\TestAction')
            ->andReturn(false);
        
        // Call the controller
        $response = $this->controller->handle($request);
        
        // Assert the response
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringContainsString('Unauthorized action', $response->getContent());
    }

    /** @test */
    public function it_executes_the_action_when_authorized()
    {
        // Define a test action class
        if (!class_exists('App\\Actions\\TestAction')) {
            eval('namespace App\\Actions; class TestAction { public function execute($param = "default") { return ["result" => $param]; } }');
        }
        
        // Create a request with the test action
        $request = new Request();
        $request->merge([
            'action' => 'TestAction',
            'params' => ['test-param'],
        ]);
        
        // Set up the request headers
        $request->headers->set('X-Inertia-Component', 'Test');
        
        // Set up the authorizer to allow the action
        $this->authorizer->shouldReceive('isAuthorized')
            ->once()
            ->with('App\\Actions\\TestAction')
            ->andReturn(true);
        
        // Mock Inertia to capture the response
        $this->mock('Inertia\\Inertia', function ($mock) {
            $mock->shouldReceive('render')
                ->once()
                ->andReturnUsing(function ($component, $props) {
                    $this->assertEquals('test-param', $props['aghanim']['actionResult']['result']);
                    return response()->json($props);
                });
        });
        
        // Call the controller
        $response = $this->controller->handle($request);
        
        // Assert the response contains the action result
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('test-param', $content['aghanim']['actionResult']['result']);
    }

    /** @test */
    public function it_validates_the_request()
    {
        // Create a request with missing required fields
        $request = new Request();
        
        // Call the controller
        $response = $this->controller->handle($request);
        
        // Assert the response
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('action', $response->getContent());
    }
}
