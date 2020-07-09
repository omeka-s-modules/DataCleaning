<?php
namespace DataCleaning\Controller\Admin;

use Laminas\Session\Container;
use DataCleaning\Form\AuditForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $form = $this->getForm(AuditForm::class);
        $form->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'audit'], true));

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function auditAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }

        $form = $this->getForm(AuditForm::class);
        $form->setData($this->params()->fromPost());
        if (!$form->isValid()) {
            $this->messenger()->addFormErrors($form);
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        $formData = $form->getData();

        parse_str($formData['item_query'], $itemQuery);
        $itemIds = $this->dataCleaning()->getItemIds($itemQuery);
        list($valuesStmt, $valuesUniqueCount, $valuesTotalCount) = $this->dataCleaning()->getValues(
            $itemIds,
            $formData['property_id'],
            $formData['data_type_name'],
            $formData['audit_column']
        );

        $property = $this->api()->read('properties', $formData['property_id'])->getContent();
        $dataType = $this->dataCleaning()->getDataType($formData['data_type_name']);

        $view = new ViewModel;
        $view->setVariable('itemQuery', $itemQuery);
        $view->setVariable('itemIds', $itemIds);
        $view->setVariable('property', $property);
        $view->setVariable('dataType', $dataType);
        $view->setVariable('auditColumn', $formData['audit_column']);
        $view->setVariable('valuesStmt', $valuesStmt);
        $view->setVariable('valuesUniqueCount', $valuesUniqueCount);
        $view->setVariable('valuesTotalCount', $valuesTotalCount);
        return $view;
    }

    public function cleanAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        print_r($this->params()->fromPost());
        exit;
    }

    public function validateAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        $value = $this->params()->fromPost('value');
        $dataTypeName = $this->params()->fromPost('data_type_name');
        $dataType = $this->dataCleaning()->getDataType($dataTypeName);
        return new JsonModel([
            'isValid' => $dataType->isValid(['@value' => $value]),
        ]);
    }
}
