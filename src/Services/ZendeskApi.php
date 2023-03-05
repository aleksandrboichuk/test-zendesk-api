<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ZendeskApi
{
    /**
     * Guzzle Http client
     *
     * @var Client
     */
    protected Client $client;

    /**
     * Zendesk basic API URL
     *
     * @var string
     */
    protected string $api_url = "https://test.zendesk.com/api/v2";

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setClient();
    }

    /**
     * Встановлення клієнту Guzzle для запитів на API
     *
     * @return void
     */
    private function setClient(): void
    {
        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'auth' => [
                'test@gmail.com',
                'password'
            ]
        ]);
    }

    /**
     * Повертає список елементів певної сутності з АРІ
     *
     * @param string $entity
     * @param int $per_page
     * @param int $page
     * @return array|null
     */
    public function getEntityAll(string $entity, int $per_page = 100, int $page = 1): ?array
    {
        $response = $this->sendRequest("$entity", [
            'page[size]' => $per_page,
            'page' => $page
        ]);

        return $response->$entity ?? null;
    }

    /**
     * Повертає кількість елементів певної сутності з АРІ
     *
     * @param string $entity
     * @return int
     */
    public function getEntityCount(string $entity): int
    {
        $response = $this->sendRequest("$entity/count");

        return $response->count->value;
    }

    /**
     * Повертає один елемент по його ID з АРІ
     *
     * @param   string          $entity - сутність, напр. groups, organizations
     * @param   string|null     $response_key = null - ключ у відповіді АРІ, до якого звернутись для отримання елементу
     * @param   int|null        $id - ID сутності
     * @return  \stdClass|null
     */
    public function getEntityById(string $entity, $id, string $response_key = null): ?\stdClass
    {
        // якщо відсутній айдішник, то без запиту одразу повертаємо null
        if(!$id){
            return null;
        }

        // якщо ключ для отримання відповіді вказано, то використовуємо його,
        // а якщо ні - приводимо до однини назву нашої сутності та використовуємо у якості ключа
        $response_key = $response_key ?? preg_replace('/s$/', '', $entity);

        $response = $this->sendRequest("$entity/$id");

        return $response->$response_key ?? null;
    }

    /**
     * Повертає коменти тікетів з АРІ
     *
     * @param int $ticket_id - ID тікета
     * @return \stdClass|null
     */
    public function getComments(int $ticket_id): ?\stdClass
    {
        $response = $this->sendRequest("tickets/$ticket_id/comments");

        return $response?->comments;
    }

    /**
     * Відправка запиту на API
     *
     * @param string $url
     * @param array $options
     * @return \stdClass
     */
    private function sendRequest(string $url, array $options = []): \stdClass
    {
        try {

            $response = $this->client->get("$this->api_url/$url", $options);

        } catch (GuzzleException $exception) {

            echo $exception->getMessage();
            exit;

        }

        return json_decode($response->getBody());
    }
}