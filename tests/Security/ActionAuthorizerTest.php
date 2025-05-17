<?php

namespace BangNokia\Aghanim\Tests\Security;

use BangNokia\Aghanim\Security\ActionAuthorizer;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;

class ActionAuthorizerTest extends TestCase
{
    protected ActionAuthorizer $authorizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizer = new ActionAuthorizer();
    }

    /** @test */
    public function it_authorizes_all_actions_when_config_is_set_to_all()
    {
        Config::set('aghanim.authorized_actions', 'all');
        
        $this->assertTrue($this->authorizer->isAuthorized('App\\Actions\\TestAction'));
        $this->assertTrue($this->authorizer->isAuthorized('App\\Actions\\AnotherAction'));
    }

    /** @test */
    public function it_only_authorizes_actions_in_the_list_when_config_is_an_array()
    {
        Config::set('aghanim.authorized_actions', [
            'App\\Actions\\TestAction',
            'App\\Actions\\AllowedAction',
        ]);
        
        $this->assertTrue($this->authorizer->isAuthorized('App\\Actions\\TestAction'));
        $this->assertTrue($this->authorizer->isAuthorized('App\\Actions\\AllowedAction'));
        $this->assertFalse($this->authorizer->isAuthorized('App\\Actions\\DisallowedAction'));
    }

    /** @test */
    public function it_returns_false_for_invalid_configuration()
    {
        Config::set('aghanim.authorized_actions', 123); // Invalid config
        
        $this->assertFalse($this->authorizer->isAuthorized('App\\Actions\\TestAction'));
    }

    /** @test */
    public function it_returns_all_authorized_actions_when_config_is_set_to_all()
    {
        // Mock the file system
        $this->mock_file_system();
        
        Config::set('aghanim.authorized_actions', 'all');
        Config::set('aghanim.action_namespace', 'App\\Actions');
        Config::set('aghanim.action_path', app_path('Actions'));
        
        $actions = $this->authorizer->getAuthorizedActions();
        
        $this->assertIsArray($actions);
        $this->assertContains('App\\Actions\\TestAction', $actions);
        $this->assertContains('App\\Actions\\AnotherAction', $actions);
    }

    /** @test */
    public function it_returns_only_authorized_actions_when_config_is_an_array()
    {
        Config::set('aghanim.authorized_actions', [
            'App\\Actions\\TestAction',
            'App\\Actions\\AllowedAction',
            'NonExistentClass', // This should be filtered out
        ]);
        
        $actions = $this->authorizer->getAuthorizedActions();
        
        $this->assertIsArray($actions);
        $this->assertContains('App\\Actions\\TestAction', $actions);
        $this->assertContains('App\\Actions\\AllowedAction', $actions);
        $this->assertNotContains('NonExistentClass', $actions);
    }

    /**
     * Mock the file system for testing.
     */
    private function mock_file_system()
    {
        // Define test classes for the file system mock
        if (!class_exists('App\\Actions\\TestAction')) {
            eval('namespace App\\Actions; class TestAction {}');
        }
        
        if (!class_exists('App\\Actions\\AnotherAction')) {
            eval('namespace App\\Actions; class AnotherAction {}');
        }
        
        if (!class_exists('App\\Actions\\AllowedAction')) {
            eval('namespace App\\Actions; class AllowedAction {}');
        }
        
        // Mock the File facade
        $this->mock(\Illuminate\Support\Facades\File::class, function ($mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('allFiles')->andReturn([
                new \SplFileInfo('TestAction.php'),
                new \SplFileInfo('AnotherAction.php'),
            ]);
        });
    }
}
