<?php

namespace App\Services;

use App\Enums\ValidationResultEnum;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationService
{
    public function verify($json)
    {
        $this->verifyRecipient($json);
        $this->verifyIssuer($json);
        $this->verifySignature($json);

        if (!$this->verifyRecipient($json)) {
            return ValidationResultEnum::InvalidRecipient;
        }

        if (!$this->verifyIssuer($json)) {
            return ValidationResultEnum::InvalidIssuer;
        }

        if (!$this->verifySignature($json)) {
            return ValidationResultEnum::InvalidSignature;
        }

        return ValidationResultEnum::Verified;
    }

    private function verifyRecipient($json)
    {
        $json = $json['data'];
        return isset($json['recipient']['name']) && isset($json['recipient']['email']);
    }

    private function verifyIssuer($json)
    {
        $json = $json['data'];
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
