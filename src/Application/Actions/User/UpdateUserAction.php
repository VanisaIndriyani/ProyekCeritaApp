<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateUserAction extends UserAction
{
    /**
     * Menangani permintaan untuk memperbarui profil pengguna.
     * Mengambil data dari request, memperbarui objek User,
     * dan menyimpannya melalui UserRepository.
     *
     * @return Response Respons HTTP dengan data pengguna yang diperbarui.
     */
    protected function action(): Response
    {
        $userId = (int)$this->resolveArg('id');
        $data = $this->getFormData();

        // Ambil data pengguna yang sudah ada untuk mempertahankan peran dan bidang lain yang tidak dapat diedit
        $existingUser = $this->userRepository->findUserOfId($userId);

        $username = $data['username'] ?? $existingUser->getUsername();
        $nama = $data['nama'] ?? $existingUser->getFirstName(); // 'nama' di DB
        $email = $data['email'] ?? $existingUser->getLastName(); // 'email' di DB
        $role = $existingUser->getRole(); // Peran tidak boleh diperbarui oleh pengguna, hanya oleh admin

        $user = new User($userId, $username, $nama, $email, $role);
        $updatedUser = $this->userRepository->update($user);

        $this->logger->info("Profil pengguna dengan id {$userId} diperbarui.");

        return $this->respondWithData($updatedUser);
    }
}