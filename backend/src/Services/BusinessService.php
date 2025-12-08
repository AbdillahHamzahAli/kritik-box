<?php

namespace App\Services;

use BlakvGhost\PHPValidator\Validator;
use App\Repository\UserRepository;
use App\Repository\BusinessRepository;
use App\Models\BusinessModel;
use DateTime;
use Exception;

class BusinessService
{
    private $businessRepository;
    private $userRepository;

    public function __construct(
        BusinessRepository $businessRepository,
        UserRepository $userRepository,
    ) {
        $this->businessRepository = $businessRepository;
        $this->userRepository = $userRepository;
    }

    public function createBusiness(array $data, int $userId): object
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new Exception("User not found");
        }

        $isMember = false;
        if (!empty($user->membership_expires_at)) {
            $expiryDate = new DateTime($user->membership_expires_at);
            $now = new DateTime();
            if ($expiryDate > $now) {
                $isMember = true;
            }
        }

        if (!$isMember) {
            $currentCount = $this->businessRepository->countByUserId($userId);
            if ($currentCount >= 2) {
                throw new Exception(
                    "Free Plan Limit Reached. Upgrade to Premium to create more businesses.",
                    401,
                );
            }
        }
        $validator = new Validator($data, [
            "name" => "required|string",
            "location" => "required|string",
            "address" => "string",
        ]);

        if (!$validator->isValid()) {
            throw new Exception(json_encode($validator->getErrors()));
        }

        $uniqueCode = "";
        do {
            $uniqueCode = $this->generateRandomString(6);
            $exists = $this->businessRepository->isQrCodeExists($uniqueCode);
        } while ($exists);

        $reqBusiness = new BusinessModel(
            null,
            $userId,
            $data["name"],
            $data["location"],
            $data["address"] ?? "",
            $uniqueCode,
        );

        $createdBusiness = $this->businessRepository->create($reqBusiness);
        if (!$createdBusiness) {
            throw new Exception("Gagal membuat business di database");
        }

        return (object) [
            "id" => $createdBusiness->id,
            "name" => $createdBusiness->name,
            "location" => $createdBusiness->location,
            "address" => $createdBusiness->address,
            "qrcode" => $createdBusiness->qrcode,
        ];
    }

    public function updateBusiness(array $data, int $id, int $userId): object
    {
        $existingBusiness = $this->businessRepository->findById($id);

        if (!$existingBusiness) {
            throw new Exception("Business not found");
        }

        if ($existingBusiness->user_id !== $userId) {
            throw new Exception("Unauthorized: You do not own this business");
        }

        $validator = new Validator($data, [
            "name" => "required|string",
            "location" => "required|string",
            "address" => "string",
        ]);

        if (!$validator->isValid()) {
            throw new Exception(json_encode($validator->getErrors()));
        }

        $existingBusiness->name = $data["name"];
        $existingBusiness->location = $data["location"];
        $existingBusiness->address =
            $data["address"] ?? $existingBusiness->address;

        $isUpdated = $this->businessRepository->update($existingBusiness);

        if (!$isUpdated) {
            throw new Exception("Failed to update business");
        }

        return (object) [
            "id" => $existingBusiness->id,
            "name" => $existingBusiness->name,
            "location" => $existingBusiness->location,
            "address" => $existingBusiness->address,
            "qrcode" => $existingBusiness->qrcode,
        ];
    }

    public function getAll(int $userId): ?array
    {
        $businesses = $this->businessRepository->findByUserId($userId);

        if (!$businesses) {
            return null;
        }

        return array_map(function ($business) {
            return [
                "id" => $business->id,
                "name" => $business->name,
                "location" => $business->location,
                "address" => $business->address,
                "qrcode" => $business->qrcode,
            ];
        }, $businesses);
    }

    public function getById(int $id, int $userId): object
    {
        $business = $this->businessRepository->findById($id);

        if (!$business || $business->user_id !== $userId) {
            throw new Exception("Business not found");
        }

        return (object) [
            "id" => $business->id,
            "name" => $business->name,
            "location" => $business->location,
            "address" => $business->address,
            "qrcode" => $business->qrcode,
        ];
    }

    public function delete(int $id, int $userId): bool
    {
        $existingBusiness = $this->businessRepository->findById($id);

        if (!$existingBusiness || $existingBusiness->user_id !== $userId) {
            throw new Exception("Business not found");
        }

        $isDeleted = $this->businessRepository->delete($id);
        if (!$isDeleted) {
            throw new Exception("Failed to delete business");
        }

        return true;
    }

    public function getPublicBusinessByCode(string $code): object
    {
        $business = $this->businessRepository->findByQrCode($code);

        if (!$business) {
            throw new Exception("Business not found");
        }

        return (object) [
            "name" => $business->name,
            "location" => $business->location,
            "address" => $business->address,
            "unique_code" => $business->qrcode,
        ];
    }

    private function generateRandomString(int $length = 6): string
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
