<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Sanctum\PersonalAccessToken;

class VerificationController extends Controller
{
    public function upload()
    {
        $token = session('token');

        if (!$token) {
            return redirect()->route('login');
        }

        return view('upload', [
            'token' => session('token')]
        );
    }

    public function verify(Request $request): JsonResource
    {
        $file = $request->file('file');

        $fileType = $file->getClientMimeType();

        $user = PersonalAccessToken::findToken(session('token'));

        $allowedTypes = ['application/json'];
        if (!in_array($fileType, $allowedTypes)) {
            return new JsonResource(['error' => 'Invalid file type. Only supports JSON for now.']);
        }

        $jsonContent = json_decode(file_get_contents($file->path()), true);

        $errors = "";

        if (!$this->verifyRecipient($jsonContent['data'])) {
            $errors = 'invalid_recipient';
        }

        elseif (!$this->verifyIssuer($jsonContent['data'])) {
            $errors = 'invalid_issuer';
        }

        elseif (!$this->verifySignature($jsonContent)) {
            $errors = 'invalid_signature';
        }

        if ($errors) {
            // return new JsonResponse(null, Response::HTTP_NO_CONTENT);
            $jsonContent['result'] = $errors;
        }
        else {
            $jsonContent['result'] = 'verified';
        }

        $jsonContent['file_type'] = $fileType;
        $jsonContent['user_id'] = Auth::user()->id;

        Document::create($jsonContent);

        return new DocumentResource($jsonContent);
    }

    private function verifyRecipient($json)
    {
        return isset($json['recipient']['name']) && isset($json['recipient']['email']);
    }

    private function verifyIssuer($json)
    {
        if (!isset($json['issuer']['name']) || !isset($json['issuer']['identityProof'])) {
            return false;
        }

        $key = $json['issuer']['identityProof']['key'];
        $location = $json['issuer']['identityProof']['location'];

        $response = Http::get("https://dns.google/resolve", [
            'name' => $location,
            'type' => 'TXT'
        ]);

        $dnsRecords = $response->json()['Answer'] ?? [];

        foreach ($dnsRecords as $record) {
            if (strpos($record['data'], $key) !== false) {
                return true;
            }
        }

        return false;
    }

    private function verifySignature($json)
    {
        $computedHash = $this->computeTargetHash($json['data']);
        return $computedHash === $json['signature']['targetHash'];
    }

    private function computeTargetHash($data)
    {
        $hashes = [];

        foreach ($this->flattenArray($data) as $key => $value) {
            // $hashes[] = hash('sha256', json_encode([$key => $value], JSON_UNESCAPED_SLASHES));

            $hash = hash('sha256', json_encode([$key => $value], JSON_UNESCAPED_SLASHES));
            $hashes[] = $hash;
        }

        sort($hashes);

        $totalHash = hash('sha256', json_encode($hashes, JSON_UNESCAPED_SLASHES));
        return $totalHash;
    }

    private function flattenArray($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}
