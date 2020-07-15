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
        $this->setBrowseDefaults('id');
        $query = $this->params()->fromQuery();
        $query['class'] = CleanDataJob::class;
        $response = $this->api()->search('jobs', $query);
        $this->paginator($response->getTotalResults());

        $audits = [];
        foreach ($response->getContent() as $job) {
            $args = $job->args();
            list($auditColumn, $targetAuditColumn) = $this->dataCleaning()->getAuditColumnsFromData($args);
            list($property, $targetProperty) = $this->dataCleaning()->getPropertiesFromData($args);
            list($dataType, $targetDataType) = $this->dataCleaning()->getDataTypesFromData($args);
            $audits[] = [
                'job' => $job,
                'item_query' => $args['item_query'],
                'audit_column' => $auditColumn,
                'property' => $property,
                'data_type' => $dataType,
                'target_audit_column' => $targetAuditColumn,
                'target_property' => $targetProperty,
                'target_data_type' => $targetDataType,
            ];
        }

        $view = new ViewModel;
        $view->setVariable('audits', $audits);
        return $view;
    }

    public function prepareAuditAction()
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

        // Validate PrepareAuditForm.
        $form = $this->getForm(Form\PrepareAuditForm::class);
        $form->setData($this->params()->fromPost());
        if (!$form->isValid()) {
            $this->messenger()->addFormErrors($form);
            return $this->redirect()->toRoute(null, ['action' => 'prepare-audit'], true);
        }
        $formData = $form->getData();

        // Get item IDs, unique strings, and string counts.
        parse_str($formData['item_query'], $itemQuery);
        $itemIds = $this->dataCleaning()->getItemIds($itemQuery);
        list(
            $stringsStmt,
            $stringsUniqueCount,
            $stringsTotalCount
        ) = $this->dataCleaning()->getValueStrings(
            $itemIds,
            $formData['audit_column'],
            $formData['property_id'],
            $formData['data_type_name']
        );

        // Prepare form data for AuditForm.
        $formData['item_ids'] = json_encode($itemIds);
        $formData = array_merge($formData, $formData['advanced']);
        unset($formData['prepareauditform_csrf']);
        unset($formData['advanced']);

        // Set original and target parameters.
        list($auditColumn, $targetAuditColumn) = $this->dataCleaning()->getAuditColumnsFromData($formData);
        list($property, $targetProperty) = $this->dataCleaning()->getPropertiesFromData($formData);
        list($dataType, $targetDataType) = $this->dataCleaning()->getDataTypesFromData($formData);

        $form = $this->getForm(Form\AuditForm::class);
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
        unset($formData['auditform_csrf']);

        $job = $this->jobDispatcher()->dispatch(CleanDataJob::class, $formData);
        $this->messenger()->addSuccess('Cleaning data. This may take a while. You may refresh this page for status updates.'); // @translate

        return $this->redirect()->toRoute(null, ['action' => 'index'], true);
    }

    public function validateAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'index'], true);
        }
        $postData = $this->params()->fromPost();
        switch ($postData['audit_column']) {
            case 'value_resource_id':
                $validateKey = 'value_resource_id';
                break;
            case 'uri':
                $validateKey = '@id';
                break;
            case 'value':
            default:
                $validateKey = '@value';
        }
        $dataType = $this->dataCleaning()->getDataType($postData['data_type_name']);
        $responseData = [];
        foreach (json_decode($postData['validate_data'], true) as $id => $string) {
            $responseData[$id] = $dataType->isValid([$validateKey => $string]);
        }
        return new JsonModel($responseData);
    }
}
