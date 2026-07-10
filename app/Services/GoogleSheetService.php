<?php

namespace App\Services;

use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Str;

class GoogleSheetService
{
    public function __construct(protected GoogleService $googleService) {}

    protected function service(): Sheets
    {
        $client = $this->googleService->getAuthorizedClient();

        return new Sheets($client);
    }

    public function read($spreadsheetId, $range)
    {
        $service = $this->service();

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (!$rows) return [];

        $header = array_shift($rows);

        // convert header -> slug
        $header = array_map(function ($item) {
            return Str::slug($item, '_');
        }, $header);

        $data = [];

        foreach ($rows as $row) {

            if (!array_filter($row)) continue;

            $row = array_pad($row, count($header), null);
            $data[] = array_combine($header, $row);
        }

        return $data;
    }

    public function append($spreadsheetId, $range, array $values)
    {
        $service = $this->service();

        $body = new ValueRange([
            'values' => $values
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];

        return $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );
    }

    public function appendRow($spreadsheetId, $range, array $row)
    {
        return $this->append($spreadsheetId, $range, [$row]);
    }
}