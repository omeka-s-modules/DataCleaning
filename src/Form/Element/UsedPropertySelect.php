<?php
namespace DataCleaning\Form\Element;

use Doctrine\ORM\EntityManager;
use Laminas\Form\Element\Select;

class UsedPropertySelect extends Select
{
    protected $entityManager;

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getValueOptions() : array
    {
        // Only include properties that are used in the value table.
        $query = $this->entityManager->createQuery('
            SELECT DISTINCT IDENTITY(v.property) property_id, p.label
            FROM Omeka\Entity\Value v
            JOIN v.property p
            ORDER BY p.label');
        $propertyIds = array_column($query->getResult(), 'property_id');

        $valueOptions = [];
        foreach ($propertyIds as $propertyId) {
            $property = $this->entityManager->find('Omeka\Entity\Property', $propertyId);
            $vocabulary = $property->getVocabulary();
            if (!isset($valueOptions[$vocabulary->getId()])) {
                $valueOptions[$vocabulary->getId()] = [
                    'label' => $vocabulary->getLabel(),
                    'options' => [],
                ];
            }
            $valueOptions[$vocabulary->getId()]['options'][$property->getId()] = $property->getLabel();
        }
        return $valueOptions;
    }
}
