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

        $property = $this->api()->read('properties', $formData['property_id'])->getContent();
        $dataType = $this->dataCleaning()->getDataType($formData['data_type_name']);

        $form = $this->getForm(Form\AuditForm::class);
        $formData['item_ids'] = json_encode($itemIds);
        $form->setData($formData);
        $form->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'clean'], true));
        $form->setAttribute('data-validate-url', $this->url()->fromRoute(null, ['action' => 'validate'], true));

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('itemQuery', $itemQuery);
        $view->setVariable('itemIds', $itemIds);
        $view->setVariable('property', $property);
        $view->setVariable('dataType', $dataType);
        $view->setVariable('auditColumn', $formData['audit_column']);
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
        $value = $this->params()->fromPost('value');
        $dataTypeName = $this->params()->fromPost('data_type_name');
        $dataType = $this->dataCleaning()->getDataType($dataTypeName);
        return new JsonModel([
            'isValid' => $dataType->isValid(['@value' => $value]),
        ]);
    }
}
