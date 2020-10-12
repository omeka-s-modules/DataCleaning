<?php
namespace DataCleaning\Controller\Admin;

use DataCleaning\Form;
use DataCleaning\Job\CleanDataJob;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->setBrowseDefaults('id');
        $query = $this->params()->fromQuery();
        $query['class'] = CleanDataJob::class;
        $response = $this->api()->search('jobs', $query);
        $this->paginator($response->getTotalResults());

        $view = new ViewModel;
        $view->setVariable('jobs', $response->getContent());
        return $view;
    }

    public function showDetailsAction()
    {
        $jobId = $query = $this->params()->fromQuery('job_id');
        $job = $this->api()->read('jobs', $jobId)->getContent();
        $args = $job->args();
        list($auditColumn, $targetAuditColumn) = $this->dataCleaning()->getAuditColumnsFromData($args);
        list($property, $targetProperty) = $this->dataCleaning()->getPropertiesFromData($args);
        list($dataType, $targetDataType) = $this->dataCleaning()->getDataTypesFromData($args);

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('auditColumn', $auditColumn);
        $view->setVariable('property', $property);
        $view->setVariable('dataType', $dataType);
        $view->setVariable('targetAuditColumn', $targetAuditColumn);
        $view->setVariable('targetProperty', $targetProperty);
        $view->setVariable('targetDataType', $targetDataType);
        $view->setVariable('corrections', json_decode($args['corrections'], true));
        $view->setVariable('removals', json_decode($args['removals'], true));
        $view->setVariable('resourceName', $args['resource_name']);
        $view->setVariable('resourceIds', json_decode($args['resource_ids'], true));
        $view->setVariable('resourceQuery', $args['resource_query']);
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

        // Get resource IDs, unique strings, and string counts.
        parse_str($formData['resource_query'], $resourceQuery);
        $resourceIds = $this->dataCleaning()->getResourceIds($formData['resource_name'], $resourceQuery);
        list(
            $stringsStmt,
            $stringsUniqueCount,
            $stringsTotalCount
        ) = $this->dataCleaning()->getUniqueStrings(
            $resourceIds,
            $formData['audit_column'],
            $formData['property_id'],
            $formData['data_type_name']
        );

        // Prepare form data for AuditForm.
        $formData['resource_ids'] = json_encode($resourceIds);
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
        $view->setVariable('resourceName', $formData['resource_name']);
        $view->setVariable('resourceQuery', $resourceQuery);
        $view->setVariable('resourceIds', $resourceIds);
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
