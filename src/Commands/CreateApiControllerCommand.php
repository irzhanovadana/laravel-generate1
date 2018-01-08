<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Commands\Bases\ControllerCommandBase;
use CrestApps\CodeGenerator\Models\Resource;
use CrestApps\CodeGenerator\Support\Config;
use CrestApps\CodeGenerator\Support\ViewLabelsGenerator;
use CrestApps\CodeGenerator\Traits\ApiResourceTrait;

class CreateApiControllerCommand extends ControllerCommandBase
{
    use ApiResourceTrait;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new API based controller.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:api-controller
                            {model-name : The model name that this controller will represent.}
                            {--controller-name= : The name of the controler.}
                            {--controller-directory= : The directory where the controller should be created under.}
                            {--model-directory= : The path where the model should be created under.}
                            {--views-directory= : The path where the views should be created under.}
                            {--resource-file= : The name of the resource-file to import from.}
                            {--routes-prefix=default-form : Prefix of the route group.}
                            {--models-per-page=25 : The amount of models per page for index pages.}
                            {--language-filename= : The languages file name to put the labels in.}
                            {--with-form-request : This will extract the validation into a request form class.}
                            {--without-form-request : Generate the controller without the form-request file. }
                            {--with-auth : Generate the controller with Laravel auth middlewear. }
                            {--template-name= : The template name to use when generating the code.}
                            {--form-request-directory= : The directory of the form-request.}
                            {--controller-extends=default-controller : The base controller to be extend.}
                            {--with-response-methods : Generate the controller both successResponse and errorResponse methods.}
                            {--with-api-resource : Generate the controller with both api-resource and api-resource-collection classes.}
                            {--api-resource-directory= : The directory where the api-resource should be created.}
                            {--api-resource-collection-directory= : The directory where the api-resource-collection should be created.}
                            {--api-resource-name= : The api-resource file name.}
                            {--api-resource-collection-name= : The api-resource-collection file name.}
                            {--force : This option will override the controller if one already exists.}';

    /**
     * check if the base class was created during this request
     *
     * @var bool
     */
    protected $isBaseCreated = false;

    /**
     * Build the model class with the given name.
     *
     * @return string
     */
    public function handle()
    {
        $input = $this->getCommandInput();
        $resource = Resource::fromFile($input->resourceFile, $input->langFile);
        $destenationFile = $this->getDestenationFile($input->controllerName, $input->controllerDirectory);

        if ($this->hasErrors($resource, $destenationFile)) {
            return false;
        }

        if ($input->withApiResource) {
            if (!$this->isApiResourceSupported()) {
                $this->info('Api-resource is not supported in the current Laravel version. To use Api-resource, pleae upgrade to Laravel 5.5+.');
                $this->warn('*** Continuing without create api-resource! ***');
            } else {
                $this->makeApiResource($input, false)
                    ->makeApiResource($input, true);
            }
        }

        $stub = $this->getControllerStub();

        return $this->processCommonTasks($input, $resource, $stub)
            ->replaceGetValidatorMethod($stub, $this->getValidatorMethod($input, $resource->fields))
            ->replaceResponseMethods($stub, $this->getResponseMethods())
            ->replaceTransformMethod($stub, $this->getTransformMethod($input, $resource->fields))
            ->replaceValidateRequest($stub, $this->getValidateRequest($input->withFormRequest))
            ->replaceReturnSuccess($stub, $this->getReturnSuccess($input, $resource->fields, 'store'), 'store')
            ->replaceReturnSuccess($stub, $this->getReturnSuccess($input, $resource->fields, 'index'), 'index')
            ->replaceReturnSuccess($stub, $this->getReturnSuccess($input, $resource->fields, 'update'), 'update')
            ->replaceReturnSuccess($stub, $this->getReturnSuccess($input, $resource->fields, 'show'), 'show')
            ->replaceReturnSuccess($stub, $this->getReturnSuccess($input, $resource->fields, 'destroy'), 'destroy')
            ->createControllerBaseClass($input->controllerDirectory)
            ->createFile($destenationFile, $stub)
            ->info('A ' . $this->getControllerType() . ' was crafted successfully.');
    }

    /**
     * Gets any additional classes to include in the use statement
     *
     * @param object $input
     *
     * @return array
     */
    protected function getAdditionalNamespaces($input)
    {
        $additionalNamespaces = parent::getAdditionalNamespaces($input);

        if (!$input->withFormRequest) {
            $additionalNamespaces[] = 'Illuminate\Support\Facades\Validator';
        }

        if ($input->withApiResource && $this->isApiResourceSupported()) {

            $additionalNamespaces[] = $this->getApiResourceNamespace(
                $this->getApiResourceClassName($input->modelName)
            );

            $additionalNamespaces[] = $this->getApiResourceCollectionNamespace(
                $this->getApiResourceCollectionClassName($input->modelName)
            );
        }

        return $additionalNamespaces;
    }

    /**
     * Get an array of all relations that are used for relations.
     *
     * @param array $fields
     *
     * @return array
     */
    /*
    protected function getNamespacesForUsedRelations(array $fields)
    {
    // Since there is no create/edit forms in the API controller,
    // No need for any relation's namespances.

    return [];
    }
     */

    /**
     * Gets the type of the controller
     *
     * @return string
     */
    protected function getControllerType()
    {
        return 'api-controller';
    }

    /**
     * Gets the path to controllers
     *
     * @param string $file
     *
     * @return string
     */
    protected function getControllerPath($file = '')
    {
        return Config::getApiControllersPath($file);
    }

    /**
     * Gets the affirm method.
     *
     * @param (object) $input
     * @param array $fields
     *
     * @return string
     */
    protected function getValidatorMethod($input, array $fields)
    {
        if ($input->withFormRequest || $this->isApiResourceSupported()) {
            return '';
        }

        $stub = $this->getStubContent('api-controller-get-validator');

        $this->replaceValidationRules($stub, $this->getValidationRules($fields))
            ->replaceFileValidationSnippet($stub, $this->getFileValidationSnippet($fields, $input, $this->requestVariable))
            ->replaceRequestFullName($stub, $this->requestNameSpace);

        return $stub;
    }

    /**
     * Gets the return code for a giving method.
     *
     * @param object $input
     * @param array $fields
     * @param string $method
     *
     * @return string
     */
    protected function getReturnSuccess($input, array $fields, $method)
    {
        if ($input->withApiResource && $this->isApiResourceSupported()) {
            return $this->getApiResourceCall($input->modelName, $fields, $method);
        }

        return $this->getSuccessCall($input->modelName, $fields, $method);
    }

    /**
     * Gets the plain success return code for a giving method.
     *
     * @param object $input
     * @param array $fields
     * @param string $method
     *
     * @return string
     */
    protected function getSuccessCall($modelName, array $fields, $method)
    {
        $stub = $this->getStubContent('api-controller-call-' . $method . '-success-method');

        $viewLabels = new ViewLabelsGenerator($modelName, $fields, $this->isCollectiveTemplate());

        $this->replaceModelName($stub, $modelName)
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceDataVariable($stub, $this->dataVariable);

        return $stub;
    }

    /**
     * Gets the plain success return code for a giving method.
     *
     * @param object $input
     * @param array $fields
     * @param string $method
     *
     * @return string
     */
    protected function getApiResourceCall($modelName, $fields, $method)
    {
        $stub = $this->getStubContent('api-controller-call-' . $method . '-api-resource');

        $viewLabels = new ViewLabelsGenerator($modelName, $fields, $this->isCollectiveTemplate());

        $this->replaceModelName($stub, $modelName)
            ->replaceStandardLabels($stub, $viewLabels->getLabels())
            ->replaceDataVariable($stub, $this->dataVariable)
            ->replaceApiResourceClass($stub, $this->getApiResourceClassName($modelName))
            ->replaceApiResourceCollectionClass($stub, $this->getApiResourceCollectionClassName($modelName));

        return $stub;
    }

    /**
     * Gets the response methods.
     *
     * @return string
     */
    protected function getResponseMethods()
    {
        $code = '';

        if (!$this->isBaseCreated && $this->mustHaveMethod('successResponse')) {
            $code .= $this->getStubContent('api-controller-success-response-method');
        }

        if (!$this->isBaseCreated && $this->mustHaveMethod('errorResponse')) {
            $code .= $this->getStubContent('api-controller-error-response-method');
        }

        return $code;
    }

    protected function getValidateRequest($withFormRequest)
    {
        if (!$withFormRequest) {
            return $this->getStubContent('api-controller-validate');
        }

        return '';
    }

    /**
     * Created a new controller base class if one does not exists
     *
     * @param string $controllerDirectory
     *
     * @return $this
     */
    protected function createControllerBaseClass($controllerDirectory)
    {
        $filename = class_basename($this->getFullClassToExtend());

        $destenationFile = $this->getDestenationFile($filename, $controllerDirectory);

        if (!$this->isFileExists($destenationFile)) {
            // At this point the base class does not exists.
            // Create a new one
            $this->isBaseCreated = true;

            $this->createFile($destenationFile, $this->getBaseClassContent($controllerDirectory))
                ->info('A new api-controller based class was created!');
        }

        return $this;
    }

    /**
     * Gets the Controller's base class content.
     *
     * @return string
     */
    protected function getBaseClassContent($controllerDirectory)
    {
        $stub = $this->getStubContent('api-controller-base-class');

        $methods = $this->getStubContent('api-controller-success-response-method') . PHP_EOL . PHP_EOL;
        $methods .= $this->getStubContent('api-controller-error-response-method');

        $this->replaceNamespace($stub, $this->getControllersNamespace($controllerDirectory))
            ->replaceResponseMethods($stub, $methods);

        return $stub;
    }

    /**
     * Gets name of the middleware
     *
     * @return string
     */
    protected function getAuthMiddleware()
    {
        return parent::getAuthMiddleware() . ':api';
    }

    /**
     * Checks if the controller must have a giving method name
     *
     * @param string $name
     *
     * @return bool
     */
    protected function mustHaveMethod($name)
    {
        $baseClass = $this->getFullClassToExtend();

        return !method_exists($baseClass, $name);
    }

    /**
     * Executes the command that generates a migration.
     *
     * @param CrestApps\CodeGenerator\Models\ResourceInput $input
     *
     * @return $this
     */
    protected function makeApiResource($input, $isCollection = false)
    {
        $this->call(
            'create:api-resource',
            [
                'model-name' => $input->modelName,
                '--api-resource-directory' => $input->apiResourceDirectory,
                '--api-resource-collection-directory' => $input->apiResourceCollectionDirectory,
                '--api-resource-name' => $input->apiResourceName,
                '--api-resource-collection-name' => $input->apiResourceCollectionName,
                '--resource-file' => $input->resourceFile,
                '--template-name' => $input->template,
                '--collection' => $isCollection,
                '--force' => $input->force,
            ]
        );

        return $this;
    }

    /**
     * Replaces get validator method for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceGetValidatorMethod(&$stub, $name)
    {
        return $this->replaceTemplate('get_validator_method', $name, $stub);
    }

    /**
     * Replaces the response methods for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceResponseMethods(&$stub, $name)
    {
        return $this->replaceTemplate('response_methods', $name, $stub);
    }

    /**
     * Replaces return_success for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @param  string  $method
     *
     * @return $this
     */
    protected function replaceReturnSuccess(&$stub, $name, $method)
    {
        return $this->replaceTemplate($method . '_return_success', $name, $stub);
    }

    /**
     * Replaces the validator_request for the giving stub,
     *
     * @param  string  $stub
     * @param  string  $name
     *
     * @return $this
     */
    protected function replaceValidateRequest(&$stub, $name)
    {
        return $this->replaceTemplate('validator_request', $name, $stub);
    }

/**
 * Gets a clean command-line arguments and options.
 *
 * @return object
 */
    protected function getCommandInput()
    {
        $input = parent::getCommandInput();

        $input->apiResourceDirectory = trim($this->option('api-resource-directory'));
        $input->apiResourceCollectionDirectory = trim($this->option('api-resource-collection-directory'));
        $input->apiResourceName = trim($this->option('api-resource-name'));
        $input->apiResourceCollectionName = trim($this->option('api-resource-collection-name'));
        $input->withApiResource = $this->option('with-api-resource');

        return $input;
    }
}
