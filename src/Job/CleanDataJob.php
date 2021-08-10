<?php
namespace DataCleaning\Job;

use Doctrine\DBAL\Connection;
use Omeka\Api\Adapter\ResourceTitleHydrator;
use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use PDO;

class CleanDataJob extends AbstractJob
{
    public function perform()
    {
        $connection = $this->getServiceLocator()->get('Omeka\Connection');
        $entityManager = $this->getServiceLocator()->get('Omeka\EntityManager');

        $corrections = json_decode($this->getArg('corrections', '{}'), true);
        $removals = json_decode($this->getArg('removals', '[]'), true);
        $resourceIds = json_decode($this->getArg('resource_ids', '[]'), true);
        list($auditColumn, $targetAuditColumn) = $this->getAuditColumns();
        list($property, $targetProperty) = $this->getProperties();
        list($dataType, $targetDataType) = $this->getDataTypes();

        // Correct values.
        foreach ($corrections as $fromString => $toString) {
            $sql = sprintf('
                UPDATE value
                SET %s = ?, property_id = ?, type = ?%s
                WHERE %s = ?
                AND property_id = ?
                AND type = ?
                AND resource_id IN (?)',
                $targetAuditColumn,
                ($auditColumn !== $targetAuditColumn) ? sprintf(', %s = NULL', $auditColumn) : '',
                $auditColumn
            );
            $connection->executeUpdate(
                $sql,
                [
                    $toString,
                    $targetProperty->getId(),
                    $targetDataType->getName(),
                    $fromString,
                    $property->getId(),
                    $dataType->getName(),
                    $resourceIds,
                ],
                [
                    PDO::PARAM_STR,
                    PDO::PARAM_INT,
                    PDO::PARAM_STR,
                    PDO::PARAM_STR,
                    PDO::PARAM_INT,
                    PDO::PARAM_STR,
                    Connection::PARAM_INT_ARRAY,
                ]
            );
        }

        // Remove values.
        foreach ($removals as $string) {
            $sql = sprintf('
                DELETE FROM value
                WHERE %s = ?
                AND property_id = ?
                AND type = ?
                AND resource_id IN (?)',
                $auditColumn
            );
            $connection->executeUpdate(
                $sql,
                [
                    $string,
                    $property->getId(),
                    $dataType->getName(),
                    $resourceIds,
                ],
                [
                    PDO::PARAM_STR,
                    PDO::PARAM_INT,
                    PDO::PARAM_STR,
                    Connection::PARAM_INT_ARRAY,
                ]
            );
        }

        // Re-hydrate all resource titles. We do this because corrections and
        // removals may affect the display title. We must iterate every resource
        // because there's no way to tell which resources the previous UPDATE
        // and DELETE operations affected.
        $dql = '
        SELECT p FROM Omeka\Entity\Property p
        JOIN p.vocabulary v
        WHERE v.namespaceUri = :namespaceUri
        AND p.localName = :localName';
        $query = $entityManager->createQuery($dql);
        $query->setParameters([
            'namespaceUri' => 'http://purl.org/dc/terms/',
            'localName' => 'title',
        ]);
        $titleProperty = $query->getOneOrNullResult();
        foreach (array_chunk($resourceIds, 100) as $resourceIdChunk) {
            foreach ($resourceIdChunk as $resourceId) {
                $entity = $entityManager->find('Omeka\Entity\Resource', $resourceId);
                (new ResourceTitleHydrator)->hydrate($entity, $titleProperty);
            }
            // Flush and clear after each chunk to avoid reaching the memory limit.
            $entityManager->flush();
            $entityManager->clear();
        }
    }

    /**
     * Validate and return audit columns.
     *
     * @return array
     */
    protected function getAuditColumns()
    {
        $validAuditColumns = ['value', 'uri', 'value_resource_id'];
        $auditColumn = $this->getArg('audit_column');
        $targetAuditColumn = null;
        if (!in_array($auditColumn, $validAuditColumns)) {
            throw new Exception\InvalidArgumentException(sprintf('Invalid audit_column "%s"', $auditColumn));
        }
        if ($this->getArg('target_audit_column')) {
            $targetAuditColumn = $this->getArg('target_audit_column');
            if (!in_array($targetAuditColumn, $validAuditColumns)) {
                throw new Exception\InvalidArgumentException(sprintf('Invalid target_audit_column "%s"', $targetAuditColumn));
            }
        }
        if (null === $targetAuditColumn) {
            // If no target is set, set it to the original audit column.
            $targetAuditColumn = $auditColumn;
        }
        return [$auditColumn, $targetAuditColumn];
    }

    /**
     * Validate and return properties.
     *
     * find() will throw an exception if invalid.
     *
     * @return array
     */
    protected function getProperties()
    {
        $entityManager = $this->getServiceLocator()->get('Omeka\EntityManager');
        $property = $entityManager->find('Omeka\Entity\Property', $this->getArg('property_id'));
        $targetProperty = null;
        if ($this->getArg('target_property_id')) {
            $targetProperty = $entityManager->find('Omeka\Entity\Property', $this->getArg('target_property_id'));
        }
        if (null === $targetProperty) {
            // If no target is set, set it to the original property.
            $targetProperty = $property;
        }
        return [$property, $targetProperty];
    }

    /**
     * Validate and return data types.
     *
     * get() will throw an exception if invalid.
     *
     * @return array
     */
    protected function getDataTypes()
    {
        $dataTypeManager = $this->getServiceLocator()->get('Omeka\DataTypeManager');
        $dataType = $dataTypeManager->get($this->getArg('data_type_name'));
        $targetDataType = null;
        if ($this->getArg('target_data_type_name')) {
            $targetDataType = $dataTypeManager->get($this->getArg('target_data_type_name'));
        }
        if (null === $targetDataType) {
            // If no target is set, set it to the original data type.
            $targetDataType = $dataType;
        }
        return [$dataType, $targetDataType];
    }
}
