<?php

namespace BangNokia\Aghanim\Tests\Actions;

use BangNokia\Aghanim\Actions\Action;
use Illuminate\Validation\ValidationException;
use Orchestra\Testbench\TestCase;

class ActionTest extends TestCase
{
    /** @test */
    public function it_validates_parameters_against_rules()
    {
        $action = new class extends Action {
            public function execute(string $name, int $age)
            {
                $validated = $this->validate([
                    'name' => $name,
                    'age' => $age,
                ]);
                
                return $validated;
            }
            
            public function rules(): array
            {
                return [
                    'name' => 'required|string|min:3',
                    'age' => 'required|integer|min:18',
                ];
            }
        };
        
        // Valid parameters
        $result = $action->execute('John Doe', 25);
        $this->assertEquals('John Doe', $result['name']);
        $this->assertEquals(25, $result['age']);
        
        // Invalid parameters
        $this->expectException(ValidationException::class);
        $action->execute('Jo', 17);
    }
    
    /** @test */
    public function it_uses_custom_validation_messages()
    {
        $action = new class extends Action {
            public function execute(string $name)
            {
                $validated = $this->validate([
                    'name' => $name,
                ]);
                
                return $validated;
            }
            
            public function rules(): array
            {
                return [
                    'name' => 'required|string|min:3',
                ];
            }
            
            public function messages(): array
            {
                return [
                    'name.min' => 'The name must be at least 3 characters long.',
                ];
            }
        };
        
        try {
            $action->execute('Jo');
            $this->fail('ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertStringContainsString(
                'The name must be at least 3 characters long.',
                $e->validator->errors()->first('name')
            );
        }
    }
    
    /** @test */
    public function it_returns_parameters_when_no_rules_are_defined()
    {
        $action = new class extends Action {
            public function execute(string $name, int $age)
            {
                $validated = $this->validate([
                    'name' => $name,
                    'age' => $age,
                ]);
                
                return $validated;
            }
            
            // No rules defined
        };
        
        $result = $action->execute('Jo', 17);
        $this->assertEquals('Jo', $result['name']);
        $this->assertEquals(17, $result['age']);
    }
}
