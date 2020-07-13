<?php
namespace DataCleaning\Controller\Admin;

use Laminas\Session\Container;
use DataCleaning\Form;
use DataCleaning\Job\CleanDataJob;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $form = $this->getForm(Form\PrepareAuditForm::class);
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

        $form = $this->getForm(Form\PrepareAuditForm::class);
        $form->setData($this->params()->fromPost());
        if (!$form->isValid()) {
            $this->messenger()->addFormErrors($form);
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        $formData = $form->getData();

        parse_str($formData['item_query'], $itemQuery);
        $itemIds = $this->dataCleaning()->getItemIds($itemQuery);
        list($stringsStmt, $stringsUniqueCount, $stringsTotalCount) = $this->dataCleaning()->getValueStrings(
            $itemIds,
            $formData['property_id'],
            $formData['data_type_name'],
            $formData['audit_column']
        );

        $auditColumn = $formData['audit_column'];
        $targetAuditColumn = $auditColumn;
        if ($formData['target_audit_column']) {
            $targetAuditColumn = $formData['target_audit_column'];
        }
        $property = $this->api()->read('properties', $formData['property_id'])->getContent();
        $targetProperty = $property;
        if ($formData['target_property_id']) {
            $targetProperty = $this->api()->read('properties', $formData['target_property_id'])->getContent();
        }
        $dataType = $this->dataCleaning()->getDataType($formData['data_type_name']);
        $targetDataType = $dataType;
        if ($formData['target_data_type_name']) {
            $targetDataType = $this->dataCleaning()->getDataType($formData['target_data_type_name']);
        }

        $form = $this->getForm(Form\AuditForm::class);
        $formData['item_ids'] = json_encode($itemIds);
        $form->setData($formData);
        $form->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'clean'], true));
        $form->setAttribute('data-validate-url', $this->url()->fromRoute(null, ['action' => 'validate'], true));

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('itemQuery', $itemQuery);
        $view->setVariable('itemIds', $itemIds);
        $view->setVariable('auditColumn', $auditColumn);
        $view->setVariable('property', $property);
        $view->setVariable('dataType', $dataType);
        $view->setVariable('targetAuditColumn', $targetAuditColumn);
        $view->setVariable('targetProperty', $targetProperty);
        $view->setVariable('targetDataType', $targetDataType);
        $view->setVariable('stringsStmt', $stringsStmt);
        $view->setVariable('stringsUniqueCount', $stringsUniqueCount);
        $view->setVariable('stringsTotalCount', $stringsTotalCount);
        return $view;
    }

    public function cleanAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }

        $form = $this->getForm(Form\AuditForm::class);
        $form->setData($this->params()->fromPost());
        if (!$form->isValid()) {
            $this->messenger()->addFormErrors($form);
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        $formData = $form->getData();
        unset($formData['cleaningform_csrf']);

        $job = $this->jobDispatcher()->dispatch(CleanDataJob::class, $formData);
        $this->messenger()->addSuccess('Cleaning data. This may take a while.'); // @translate

        $view = new ViewModel;
        $view->setVariable('formData', $formData);
        return $view;
    }

    public function validateAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        $postData = $this->params()->fromPost();
        $dataType = $this->dataCleaning()->getDataType($postData['data_type_name']);
        $responseData = [];
        foreach (json_decode($postData['validate_data'], true) as $id => $string) {
            $responseData[$id] = $dataType->isValid([$postData['validate_key'] => $string]);
        }
        return new JsonModel($responseData);
    }
}
