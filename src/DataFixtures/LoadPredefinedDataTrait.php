<?php

namespace App\DataFixtures;

/**
 * @template T
 */
trait LoadPredefinedDataTrait
{
    /**
     * @return array<T>
     */
    private function loadCSV(string $entityName, string $file): array
    {
        $result = [];
        $data = file_get_contents($file);
        $records = explode("\n", $data);
        foreach ($records as $record) {
            $record = trim($record);
            if ('' === $record) {
                continue;
            }
            $data = explode(',', $record);
            if (!isset($headers)) {
                $headers = $this->setHeaders($data);
                continue;
            }
            if (count($headers) != count($data)) {
                break;
            }
            $entity = new $entityName();
            foreach ($data as $key => $value) {
                if ('' === $value) {
                    $value = null;
                }
                call_user_func([$entity, $headers[$key]], $value);
            }
            $result[] = $entity;
        }

        return $result;
    }

    /**
     * @param array<string> $data
     *
     * @return array<string>
     */
    private function setHeaders(array $data): array
    {
        return array_map(function ($header) {
            return 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $header)));
        }, $data);
    }
}
