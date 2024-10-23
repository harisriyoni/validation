<?php

namespace Tests\Feature;

use App\Rules\Uppercase; // Ensure this rule exists in the specified namespace
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

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
    public function testValidata()
    {
        $data = [
            "username" => "haris@gmail.com",
            "password" => "password",
            "admin" => true,
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => "required|min:6|max:20",
        ];

        $validator = Validator::make($data, $rules);
        try {
            $validata = $validator->validate();
            assertNotNull($validata);
            Log::info(json_encode($validata, JSON_PRETTY_PRINT));
        } catch (ValidationException $exception) {
            self::fail($exception->getMessage());
        }
    }
    public function testValidatorInlineMessage()
    {
        $data = [
            "username" => "asd",
            "password" => "asd",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => "required|min:6|max:10",
        ];
        $message = [
            "required" => ":attribute harus diisi atuh",
            "min" => ":attribute minimal password kamu harus 6 karakter kak",
        ];

        $validator = Validator::make($data, $rules, $message);
        assertNotNull($validator);
        assertFalse($validator->passes());
        assertTrue($validator->fails());
        $message = $validator->getMessageBag();

        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
    public function testValidator_Additional_Validation()
    {
        $data = [
            "username" => "haris@gmail.com",
            "password" => "haris@gmail.com",
        ];
        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        $validator->after(function (ValidationValidator $validator) {
            $data = $validator->getData();
            if ($data['username'] == $data['password']) {
                $validator->errors()->add('password', "Password Ga Boleh Sama dengan Username KAK");
            }
        });
        assertNotNull($validator);
        assertFalse($validator->passes());
        assertTrue($validator->fails());
        $message = $validator->getMessageBag();
        Log::info($message->toJSON(JSON_PRETTY_PRINT));
    }
    public function testValidator_Custom_rule()
{
    $data = [
        "username" => "haris@gmail.com", // Should fail Uppercase
        "password" => "haris@gmail.com",
    ];

    $rules = [
        "username" => ["required", "email", "max:100", new Uppercase()],
        "password" => ["required", "min:6", "max:20"],
    ];

    $validator = Validator::make($data, $rules);

    // Check for failure
    $this->assertTrue($validator->fails());

    // Log errors for debugging
    Log::info($validator->errors()->toJson(JSON_PRETTY_PRINT));
}


}
