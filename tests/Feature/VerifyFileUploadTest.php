<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Document;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VerifyFileUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_file_type()
    {
        Storage::fake('local');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verify', [
            'file' => UploadedFile::fake()->create('document.txt', 100, 'text/plain'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['error' => 'Invalid file type. Only supports JSON for now.']
            ]);
    }

    public function test_valid_file_verification()
    {
        Storage::fake('local');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $validJson = json_encode([
            'data' => [
                "id" => "63c79bd9303530645d1cca00",
                "name" => "Certificate of Completion",
                "recipient" => [
                    "name" => "Marty McFly",
                    "email" => "marty.mcfly@gmail.com"
                ],
                "issuer" => [
                    "name" => "Accredify",
                    "identityProof" => [
                        "type" => "DNS-DID",
                        "key" => "did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller",
                        "location" => "ropstore.accredify.io"
                    ]
                ],
                "issued" => "2022-12-23T00:00:00+08:00"
            ],
            "signature" => [
                "type" => "SHA3MerkleProof",
                "targetHash" => "288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e"
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('document.json', $validJson);

        $response = $this->actingAs($user)->postJson('/api/verify', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => ['result' => 'verified']]);
    }

    public function test_invalid_recipient()
    {
        Storage::fake('local');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $invalidJson = json_encode([
            'data' => [
                "id" => "63c79bd9303530645d1cca00",
                "name" => "Certificate of Completion",
                "recipient" => [
                    "phone" => "1234567890",
                    "age" => 25
                ],
                "issuer" => [
                    "name" => "Accredify",
                    "identityProof" => [
                        "type" => "DNS-DID",
                        "key" => "did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller",
                        "location" => "ropstore.accredify.io"
                    ]
                ],
                "issued" => "2022-12-23T00:00:00+08:00"
            ],
            "signature" => [
                "type" => "SHA3MerkleProof",
                "targetHash" => "288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e"
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('document.json', $invalidJson);

        $response = $this->actingAs($user)->postJson('/api/verify', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => ['result' => 'invalid_recipient']]);
    }

    public function test_invalid_issuer()
    {
        Storage::fake('local');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $invalidJson = json_encode([
            'data' => [
                "id" => "63c79bd9303530645d1cca00",
                "name" => "Certificate of Completion",
                "recipient" => [
                    "name" => "Marty McFly",
                    "email" => "marty.mcfly@gmail.com"
                ],
                "issuer" => [
                    "name" => "Fake University",
                "identityProof" => [
                    "type" => "DNS-DID",
                    "key" => "did:ethr:0xabcdef1234567890abcdef1234567890#controller",
                    "location" => "fakeuniversity.accredify.io"
                ]
                ],
                "issued" => "2022-12-23T00:00:00+08:00"
            ],
            "signature" => [
                "type" => "SHA3MerkleProof",
                "targetHash" => "288f94aadadf486cfdad84b9f4305f7d51eac62db18376d48180cc1dd2047a0e"
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('document.json', $invalidJson);

        $response = $this->actingAs($user)->postJson('/api/verify', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => ['result' => 'invalid_issuer']]);
    }

    public function test_invalid_signature()
    {
        Storage::fake('local');

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $validJson = json_encode([
            'data' => [
                "id" => "63c79bd9303530645d1cca00",
                "name" => "Certificate of Completion",
                "recipient" => [
                    "name" => "Marty McFly",
                    "email" => "marty.mcfly@gmail.com"
                ],
                "issuer" => [
                    "name" => "Accredify",
                    "identityProof" => [
                        "type" => "DNS-DID",
                        "key" => "did:ethr:0x05b642ff12a4ae545357d82ba4f786f3aed84214#controller",
                        "location" => "ropstore.accredify.io"
                    ]
                ],
                "issued" => "2022-12-23T00:00:00+08:00"
            ],
            "signature" => [
                "type" => "SHA3MerkleProof",
                "targetHash" => "0xabcdef1234567890abcdef12345678901234567890abcdef1234567890"
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('document.json', $validJson);

        $response = $this->actingAs($user)->postJson('/api/verify', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => ['result' => 'invalid_signature']]);
    }
}

