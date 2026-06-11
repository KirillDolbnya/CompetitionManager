<?php

namespace App\Vk;

use App\Exceptions\VkApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Random\RandomException;

class VkApiClient
{

    public function __construct(
        private readonly string $token,
        private readonly string $apiVersion,
    )
    {
    }

    /**
     * @throws VkApiException
     * @throws RandomException
     * @throws ConnectionException
     */
    public function sendMessage(string $message, int $peerId, array $keyboard = [], string $attachment = ''): void
    {
        $this->post('messages.send', [
            'peer_id' => $peerId,
            'message' => $message,
            'random_id' => random_int(1, PHP_INT_MAX),
            'keyboard' => json_encode($keyboard, JSON_UNESCAPED_UNICODE),
            'attachment' => $attachment,
        ]);
    }

    /**
     * @throws VkApiException
     * @throws ConnectionException
     */
    public function uploadImage(string $localPath, int $peerId): ?string
    {
        $uploadUrl = $this->getUploadUrl($peerId);

        $upload = $this->upload($uploadUrl, $localPath);

        if (!isset($upload['photo']) || !isset($upload['server']) || !isset($upload['hash'])) {
            throw new VkApiException('Invalid upload response from VK');
        }

        $saved = $this->saveImage($upload['photo'], $upload['server'], $upload['hash'],);

        $photo = $saved['response'][0] ?? null;

        return $photo ? "photo{$photo['owner_id']}_{$photo['id']}" : null;
    }

    /**
     * @throws VkApiException
     * @throws ConnectionException
     */
    private function getUploadUrl(int $peerId): string
    {
        $response = $this->get('photos.getMessagesUploadServer', [
            'peer_id' => $peerId,
        ]);

        return $response['response']['upload_url'] ?? throw new VkApiException('Upload URL not received');
    }

    /**
     * @throws VkApiException
     * @throws ConnectionException
     */
    private function saveImage(string $photo, string $server, string $hash): array
    {
        return $this->get('photos.saveMessagesPhoto', [
            'photo' => $photo,
            'server' => $server,
            'hash' => $hash,
        ]);
    }

    /**
     * @throws VkApiException
     * @throws ConnectionException
     */
    private function get(string $method, array $params = []): array
    {
        return $this->request(
            Http::get(
                "https://api.vk.com/method/{$method}",
                $this->credentials($params)
            )
        );
    }

    /**
     * @throws VkApiException
     * @throws ConnectionException
     */
    private function post(string $method, array $params = []): array
    {
        return $this->request(
            Http::asForm()->post(
                "https://api.vk.com/method/{$method}",
                $this->credentials($params)
            )
        );
    }

    /**
     * @throws VkApiException
     * @throws ConnectionException
     */
    private function upload(string $url, string $path): array
    {
        if (!is_file($path)) {
            throw new VkApiException("File not found: {$path}");
        }

        return $this->request(
            Http::attach(
                'photo',
                file_get_contents($path),
                basename($path)
            )->post($url)
        );
    }

    /**
     * @throws VkApiException
     */
    private function request(Response $response): array
    {
        try {
            $response->throw();
            $data = $response->json();

            if (isset($data['error'])) {
                throw new VkApiException(
                    $data['error']['error_msg'] ?? 'VK API error',
                        $data['error']['error_code'] ?? 0
                );
            }

            return $data;
        } catch (RequestException|ConnectionException $e) {
            throw new VkApiException('VK request failed', 0, $e);
        }
    }

    private function credentials(array $params): array
    {
        return array_merge($params, [
            'access_token' => $this->token,
            'v' => $this->apiVersion,
        ]);
    }
}
