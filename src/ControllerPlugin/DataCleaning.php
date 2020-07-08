<?php
namespace DataCleaning\ControllerPlugin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
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

    public function getValues(array $resourceIds, $propertyId, $dataTypeName, $auditColumn)
    {
        $auditColumn = ('uri' === $auditColumn) ? 'uri' : 'value';
        $conn = $this->services->get('Omeka\Connection');

        // Get the values statement.
        $sql = sprintf('
        SELECT COUNT(v.%1$s) count, v.%1$s audit_column
        FROM value v
        WHERE resource_id IN (?)
        AND property_id = ?
        AND type = ?
        GROUP BY v.%1$s
        ORDER BY count DESC, audit_column ASC', $auditColumn);
        $valuesStmt = $conn->executeQuery(
            $sql,
            [$resourceIds, $propertyId, $dataTypeName],
            [Connection::PARAM_INT_ARRAY]
        );
        $valuesStmt->setFetchMode(FetchMode::NUMERIC);

        // Get the unique values count.
        $sql = sprintf('
        SELECT COUNT(*) FROM
        (
            SELECT 1
            FROM value v
            WHERE resource_id IN (?)
            AND property_id = ?
            AND type = ?
            GROUP BY v.%1$s
        ) subquery', $auditColumn);
        $valuesUniqueCount = $conn->executeQuery(
            $sql,
            [$resourceIds, $propertyId, $dataTypeName],
            [Connection::PARAM_INT_ARRAY]
        )->fetchColumn();

        // Get the total values count.
        $sql = sprintf('
        SELECT COUNT(*) FROM
        (
            SELECT 1
            FROM value v
            WHERE resource_id IN (?)
            AND property_id = ?
            AND type = ?
        ) subquery', $auditColumn);
        $valuesTotalCount = $conn->executeQuery(
            $sql,
            [$resourceIds, $propertyId, $dataTypeName],
            [Connection::PARAM_INT_ARRAY]
        )->fetchColumn();

        return [$valuesStmt, $valuesUniqueCount, $valuesTotalCount];
    }
}
