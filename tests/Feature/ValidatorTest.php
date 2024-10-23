<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use Illuminate\Validation\ValidationException;

class ValidatorTest extends TestCase
{
    public function testValidator()
    {
        $data = [
            "username" => "haris",
            "password" => "password",
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);
        assertNotNull($validator);
        assertTrue($validator->passes());
        assertFalse($validator->fails());
    }
    public function testValidatorInvalid()
    {
        $data = [
            "username" => "",
            "password" => "",
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);
        assertNotNull($validator);
        assertFalse($validator->passes());
        assertTrue($validator->fails());
        $message = $validator->getMessageBag();

        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
    public function testValidationException()
    {
        $data = [
            "username" => "",
            "password" => "",
        ];
        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);
        try {
            $validator->validate();
            self::fail("Validation did not throw an exception");
        } catch (ValidationException $exception) {
            assertNotNull($exception->validator);
            $message = $validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }
    public function testValidatorMultipleRules()
    {
        $data = [
            "username" => "haris@gmail.com",
            "password" => "password",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        assertNotNull($validator);
        assertTrue($validator->passes());
        assertFalse($validator->fails());
        $message = $validator->getMessageBag();
        Log::info($message->toJSON(JSON_PRETTY_PRINT));
    }
    public function testValidatorMultipleRulesInvalid()
    {
        $data = [
            "username" => "haris",
            "password" => "pas",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        assertNotNull($validator);
        assertFalse($validator->passes());
        assertTrue($validator->fails());
        $message = $validator->getMessageBag();
        Log::info($message->toJSON(JSON_PRETTY_PRINT));
    }
}
