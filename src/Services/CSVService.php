<?php

namespace App\Services;

class CSVService
{
    /**
     * Сервіс АРІ Zendesk
     *
     * @var ZendeskApi
     */
    protected ZendeskApi $zendeskApi;

    /**
     * Масив даних для CSV файлу
     *
     * @var array
     */
    protected array $csv_data;

    /**
     * Заголовки csv файлу
     * 
     * @var array|string[] 
     */
    protected array $headers = [
        'Ticket ID',
        'Description',
        'Status',
        'Priority',
        'Agent ID',
        'Agent Name',
        'Agent Email',
        'Contact ID',
        'Contact Name',
        'Contact Email',
        'Group ID',
        'Group Name',
        'Company ID',
        'Company Name',
        'Comments'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setZendeskApiService();

        // встановлюємо спочатку хедери csv файлу
        $this->setCSVArray($this->headers);
    }

    /**
     * Метод-сеттер для сервісу АРІ Zendesk
     *
     * @return void
     */
    private function setZendeskApiService(): void
    {
        $this->zendeskApi = new ZendeskApi();
    }

    /**
     * Метод, що встановлює заголовки csv файлу
     *
     * @param array $data
     * @return void
     */
    private function setCSVArray(array $data): void
    {
        $this->csv_data[] = $data;
    }

    /**
     * Генерує csv файл та завантажує його при запиті
     *
     * @return void
     */
    public function downloadCSV(): void
    {
        header('Content-Type: text/csv; charset=utf-8');

        header('Content-Disposition: attachment; filename=tickets.csv');

        $output = fopen('php://output', 'w');

        $data = $this->getDataForCSV();

        foreach ($data as $data_item) {
            fputcsv($output, $data_item);
        }
    }

    /**
     * Повертає масив з даними для зберігання у файл .csv
     *
     * @return array
     */
    private function getDataForCSV(): array
    {
        $per_page = 100;
        //отримуємо загальну кількість тікетів
        $count_tickets = $this->zendeskApi->getEntityCount('tickets');
        // розраховуємо кількість сторінок пагінації
        $pages = $this->getPagesCount($per_page, $count_tickets);
        // проходимось по всіх сторінках пагінації
        for($i = 1; $i <= $pages; $i++){
            // отримуємо тікети з поточної сторінки
            $tickets = $this->zendeskApi->getEntityAll('tickets', $per_page, $i);

            foreach ($tickets as $ticket) {
               $this->prepareCSVArray($ticket);
            }
        }

        return $this->csv_data;
    }

    /**
     * Формує глобальний масив для зберігання у csv
     *
     * @param \stdClass $ticket
     * @return void
     */
    private function prepareCSVArray(\stdClass $ticket): void
    {
        // отримуємо усі необхідні сутності, щоб дістати їх не вказані у тікетах властивості
        $agent = $this->zendeskApi->getEntityById('users', $ticket->assignee_id);

        $requester = $this->zendeskApi->getEntityById('users', $ticket->requester_id);

        $group = $this->zendeskApi->getEntityById('groups', $ticket->group_id);

        $company = $this->zendeskApi->getEntityById('organizations', $ticket->organization_id);

        $comments = $this->getCommentsArray($ticket->id);

        // зберігаємо у масив запис
        $this->setEntryToCSVArray($ticket, $agent, $requester, $group, $company, $comments);
    }

    /**
     * Повертає масив з тілами коментарів, отриманих з АРІ
     *
     * @param int $ticket_id
     * @return array
     */
    private function getCommentsArray(int $ticket_id): array
    {
        $comments_response = $this->zendeskApi->getComments($ticket_id);

        $comments = [];

        if($comments_response)
        {
            foreach ($comments_response as $comment) {
                $comments[] = $comment->body;
            }
        }

        return $comments;
    }

    /**
     * Повертає кількість сторінок пагінації для їх перебору
     *
     * @param int $per_page
     * @param int $elements_count
     * @return int
     */
    private function getPagesCount(int $per_page, int $elements_count): int
    {
        return intval($elements_count / $per_page) + 1;
    }

    /**
     * Записує у глобальний масив дані
     *
     * @param \stdClass|null $ticket
     * @param \stdClass|null $agent
     * @param \stdClass|null $requester
     * @param \stdClass|null $group
     * @param \stdClass|null $company
     * @param array $comments
     * @return void
     */
    private  function setEntryToCSVArray(
        ?\stdClass  $ticket,
        ?\stdClass $agent,
        ?\stdClass $requester,
        ?\stdClass $group,
        ?\stdClass $company,
        array      $comments
    ): void
    {
        $this->setCSVArray([
            // ticket
            $ticket->id,
            // прибираємо зайві розриви рядка
            trim(preg_replace('/\s\s+/', ' ', $ticket->description)),
            $ticket->status,
            $ticket->priority,

            // agent
            // у деяких тікетах можуть бути null певні властивості, які нам потрібні
            // тому перевіряємо їх наявність
            $agent ? $agent->id : '',
            $agent ? $agent->name : '',
            $agent ? $agent->email : '',

            // contact
            $requester ? $requester->id : '',
            $requester ? $requester->name : '',
            $requester ? $requester->email : '',

            // group
            $group ? $group->id : '',
            $group ? $group->name : '',

            // company (organization)
            $company ? $company->id : '',
            $company ? $company->name : '',

            // comments
            // оскільки коментарів може бути багато, то поєднуємо їх у рядок і прибираємо зайві розриви рядка
            trim(preg_replace('/\s\s+/', ' ',  implode('; ', $comments)))
        ]);
    }
}