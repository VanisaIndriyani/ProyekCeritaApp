<?php

declare(strict_types=1);

namespace App\Application\Actions\Story;

use App\Domain\Story\Story;
use Psr\Http\Message\ResponseInterface as Response;

class CreateStoryAction extends StoryAction
{
    protected function action(): Response
    {
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
        $story = new Story(
            null,
            (int)$data['userId'],
            $data['title'],
            $data['content'],
            $data['category'],
            $coverImageName,
            date('Y-m-d H:i:s')
        );
        $saved = $this->storyRepository->save($story);
        $this->logger->info("Story created.");
        return $this->respondWithData($saved, 201);
    }
} 