<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Enums\ValidationResultEnum;
use Illuminate\Support\Facades\Auth;
use App\Services\VerificationService;
use App\Http\Resources\DocumentResource;
use App\Http\Requests\VerificationRequest;
use App\Http\Controllers\Controller;

class VerificationController extends Controller
{
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    public function __invoke(VerificationRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $jsonContent = json_decode(file_get_contents($file->path()), true);

        $fileType = $file->getClientMimeType();

        $verificationResult = $this->verificationService->verify($jsonContent);
        $jsonContent['result'] = $verificationResult;

        $jsonContent['file_type'] = $fileType;
        $jsonContent['user_id'] = Auth::user()->id;

        if ($verificationResult == ValidationResultEnum::Verified) {
            Document::create($jsonContent);
        }

        return new JsonResponse(new DocumentResource($jsonContent), Response::HTTP_OK);
    }
}
