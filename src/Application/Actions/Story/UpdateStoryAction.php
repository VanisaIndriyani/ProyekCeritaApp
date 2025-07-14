<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use App\Domain\Story\Story;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateStoryAction extends StoryAction
{
    protected function action(): Response
    {
        try {
            $id = (int)$this->resolveArg('id');
            $data = $this->getFormData();
            $uploadedFiles = $this->request->getUploadedFiles();
            $coverImageName = $data['coverImage'] ?? null;
            if (isset($uploadedFiles['coverImage'])) {
                $coverImage = $uploadedFiles['coverImage'];
                if ($coverImage->getError() === UPLOAD_ERR_OK) {
                    $extension = pathinfo($coverImage->getClientFilename(), PATHINFO_EXTENSION);
                    $basename = bin2hex(random_bytes(8));
                    $filename = sprintf('%s.%0.8s', $basename, $extension);
                    $uploadDir = __DIR__ . '/../../../../public/uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $coverImage->moveTo($uploadDir . $filename);
                    $coverImageName = $filename;
                }
            }
            // Validasi field wajib
            if (empty($data['userId']) || empty($data['title']) || empty($data['content']) || empty($data['category'])) {
                return $this->respondWithData(['error' => 'Field wajib tidak boleh kosong'], 400);
            }
            $story = new Story(
                $id,
                $data['userId'],
                $data['title'],
                $data['content'],
                $data['category'],
                $coverImageName,
                $data['createdAt'] ?? date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            );
            $updated = $this->storyRepository->update($story);
            $this->logger->info("Story with id $id updated.");
            return $this->respondWithData($updated);
        } catch (\Throwable $e) {
            $this->logger->error('Update story error: ' . $e->getMessage());
            return $this->respondWithData(['error' => $e->getMessage()], 500);
        }
    }
} 