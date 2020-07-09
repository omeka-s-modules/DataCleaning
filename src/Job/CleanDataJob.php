<?php
namespace DataCleaning\Job;

use Doctrine\DBAL\Connection;
use Omeka\Job\AbstractJob;
use PDO;

class CleanDataJob extends AbstractJob
{
    public function perform()
    {
        $conn = $this->getServiceLocator()->get('Omeka\Connection');

        $corrections = json_decode($this->getArg('corrections', '{}'), true);
        $removals = json_decode($this->getArg('removals', '[]'), true);
        $itemIds = json_decode($this->getArg('item_ids', '[]'), true);
        $auditColumn = in_array($this->getArg('audit_column'), ['value', 'uri'])
            ? $this->getArg('audit_column') : 'value';

        // Correct values.
        foreach ($corrections as $fromText => $toText) {
            $sql = sprintf('
                UPDATE value
                SET %1$s = ?
                WHERE %1$s = ?
                AND property_id = ?
                AND type = ?
                AND resource_id IN (?)', $auditColumn);
            $conn->executeUpdate(
                $sql,
                [
                    $toText,
                    $fromText,
                    $this->getArg('property_id'),
                    $this->getArg('data_type_name'),
                    $itemIds,
                ],
                [
                    PDO::PARAM_STR,
                    PDO::PARAM_STR,
                    PDO::PARAM_INT,
                    PDO::PARAM_STR,
                    Connection::PARAM_INT_ARRAY,
                ]
            );
        }

        // Remove values.
        foreach ($removals as $text) {
            $sql = sprintf('
                DELETE FROM value
                WHERE %1$s = ?
                AND property_id = ?
                AND type = ?
                AND resource_id IN (?)', $auditColumn);
            $conn->executeUpdate(
                $sql,
                [
                    $text,
                    $this->getArg('property_id'),
                    $this->getArg('data_type_name'),
                    $itemIds,
                ],
                [
                    PDO::PARAM_STR,
                    PDO::PARAM_INT,
                    PDO::PARAM_STR,
                    Connection::PARAM_INT_ARRAY,
                ]
            );
        }
    }
}
