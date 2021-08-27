<?php
namespace DataCleaning\ControllerPlugin;

use DataCleaning\DataType\Unknown;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use PDO;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;

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

    /**
     * Get a data type.
     *
     * @param string $dataTypeName
     * @return \Omeka\DataType\DataTypeInterface
     */
    public function getDataType($dataTypeName)
    {
        try {
            return $this->services->get('Omeka\DataTypeManager')->get($dataTypeName);
        } catch (ServiceNotFoundException $e) {
            // Account for unregistered data types.
            return new Unknown($dataTypeName);
        }
    }

    /**
     * Get resource IDs from a query.
     *
     * @param string $resourceName
     * @param array $query
     * @return array
     */
    public function getResourceIds($resourceName, array $query)
    {
        $controller = $this->getController();
        $validResourceNames = ['items', 'item_sets', 'media'];
        $resourceName = in_array($resourceName, $validResourceNames) ? $resourceName : 'items';
        $response = $controller->api()->search($resourceName, $query, ['returnScalar' => 'id']);
        return $response->getContent();
    }

    /**
     * Get unique strings and counts from the passed resources.
     *
     * @param array $resourceIds
     * @param string $auditColumn
     * @param int $propertyId
     * @param string $dataTypeName
     * @return array Contains the unique strings, unique count, and total count
     */
    public function getUniqueStrings(array $resourceIds, $auditColumn, $propertyId, $dataTypeName)
    {
        $conn = $this->services->get('Omeka\Connection');
        $validAuditColumns = ['value', 'uri', 'value_resource_id'];
        $auditColumn = in_array($auditColumn, $validAuditColumns) ? $auditColumn : 'value';

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

    /**
     * Get original and target audit columns.
     *
     * @param array $data
     * @return array An array of strings
     */
    public function getAuditColumnsFromData(array $data)
    {
        $auditColumn = $data['audit_column'];
        $targetAuditColumn = $auditColumn;
        if ($data['target_audit_column']) {
            $targetAuditColumn = $data['target_audit_column'];
        }
        return [$auditColumn, $targetAuditColumn];
    }

    /**
     * Get original and target properties.
     *
     * @param array $data
     * @return array An arry of property objects
     */
    public function getPropertiesFromData(array $data)
    {
        $controller = $this->getController();
        $property = $controller->api()->read('properties', $data['property_id'])->getContent();
        $targetProperty = $property;
        if ($data['target_property_id']) {
            $targetProperty = $controller->api()->read('properties', $data['target_property_id'])->getContent();
        }
        return [$property, $targetProperty];
    }

    /**
     * Get original and target data types.
     *
     * @param array $data
     * @return array An arry of data type objects
     */
    public function getDataTypesFromData(array $data)
    {
        $controller = $this->getController();
        $dataType = $controller->dataCleaning()->getDataType($data['data_type_name']);
        $targetDataType = $dataType;
        if ($data['target_data_type_name']) {
            $targetDataType = $controller->dataCleaning()->getDataType($data['target_data_type_name']);
        }
        return [$dataType, $targetDataType];
    }
}
