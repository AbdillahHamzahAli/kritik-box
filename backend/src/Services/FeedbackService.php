<?php

namespace App\Services;

use BlakvGhost\PHPValidator\Validator;
use App\Repository\FeedbackRepository;
use App\Repository\BusinessRepository;
use App\Models\FeedbackModel;
use Exception;

class FeedbackService
{
    private $feedbackRepository;
    private $businessRepository;

    public function __construct(
        FeedbackRepository $feedbackRepository,
        BusinessRepository $businessRepository,
    ) {
        $this->feedbackRepository = $feedbackRepository;
        $this->businessRepository = $businessRepository;
    }

    public function createFeedback(array $data, string $uniqueCode): object
    {
        $existingBusiness = $this->businessRepository->findByQrCode(
            $uniqueCode,
        );

        if (!$existingBusiness) {
            throw new Exception("Business not found");
        }

        $validator = new Validator($data, [
            "rating" => "required|numeric",
            "text" => "string",
        ]);

        if (!$validator->isValid()) {
            throw new Exception(json_encode($validator->getErrors()));
        }

        $reqFeedback = new FeedbackModel(
            null,
            $existingBusiness->id,
            $data["rating"],
            $data["text"],
        );

        $createdFeedback = $this->feedbackRepository->create($reqFeedback);
        if (!$createdFeedback) {
            throw new Exception("Gagal membuat feedback di database");
        }

        return (object) [
            "id" => $createdFeedback->id,
            "business_id" => $createdFeedback->business_id,
            "rating" => $createdFeedback->rating,
            "text" => $createdFeedback->text,
        ];
    }

    public function getAll(int $businessId, int $userId): ?array
    {
        $existingBusiness = $this->businessRepository->findById($businessId);

        if (!$existingBusiness || $existingBusiness->user_id !== $userId) {
            throw new Exception("Business not found");
        }

        $feedbacks = $this->feedbackRepository->getAllByBusinessId($businessId);
        return $feedbacks;
    }
}
