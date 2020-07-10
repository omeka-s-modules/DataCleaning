<?php
namespace DataCleaning\ControllerPlugin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use PDO;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;

class DataCleaning extends AbstractPlugin
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $services;

    /**
     * @param ServiceLocatorInterface $services
     */
    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    public function getDataType($dataTypeName)
    {
        return $this->services->get('Omeka\DataTypeManager')->get($dataTypeName);
    }

    public function getItemIds(array $query)
    {
        $controller = $this->getController();
        $response = $controller->api()->search('items', $query, ['returnScalar' => 'id']);
        return $response->getContent();
    }

    public function getValueStrings(array $resourceIds, $propertyId, $dataTypeName, $auditColumn)
    {
        $auditColumn = ('uri' === $auditColumn) ? 'uri' : 'value';
        $conn = $this->services->get('Omeka\Connection');

        // Get the strings statement.
        $sql = sprintf('
            SELECT COUNT(%1$s) count, %1$s
            FROM value
            WHERE resource_id IN (?)
            AND property_id = ?
            AND type = ?
            GROUP BY %1$s
            ORDER BY count DESC, %1$s ASC', $auditColumn);
        $stringsStmt = $conn->executeQuery(
            $sql,
            [$resourceIds, $propertyId, $dataTypeName],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_INT, PDO::PARAM_STR]
        );
        $stringsStmt->setFetchMode(FetchMode::NUMERIC);

        // Get the unique strings count.
        $sql = sprintf('
            SELECT COUNT(*) FROM
            (
                SELECT 1
                FROM value
                WHERE resource_id IN (?)
                AND property_id = ?
                AND type = ?
                GROUP BY %1$s
            ) subquery', $auditColumn);
        $stringsUniqueCount = $conn->executeQuery(
            $sql,
            [$resourceIds, $propertyId, $dataTypeName],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_INT, PDO::PARAM_STR]
        )->fetchColumn();

        // Get the total strings count.
        $sql = sprintf('
            SELECT COUNT(*) FROM
            (
                SELECT 1
                FROM value
                WHERE resource_id IN (?)
                AND property_id = ?
                AND type = ?
            ) subquery', $auditColumn);
        $stringsTotalCount = $conn->executeQuery(
            $sql,
            [$resourceIds, $propertyId, $dataTypeName],
            [Connection::PARAM_INT_ARRAY, PDO::PARAM_INT, PDO::PARAM_STR]
        )->fetchColumn();

        return [$stringsStmt, $stringsUniqueCount, $stringsTotalCount];
    }
}
