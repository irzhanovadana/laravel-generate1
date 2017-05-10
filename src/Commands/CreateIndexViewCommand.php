<?php

namespace CrestApps\CodeGenerator\Commands;

use CrestApps\CodeGenerator\Support\ViewsCommand;
use CrestApps\CodeGenerator\Support\GenerateFormViews;

class CreateIndexViewCommand extends ViewsCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:index-view
                            {model-name : The model name that this view will represent.}
                            {--fields= : The fields to define the model.}
                            {--fields-file= : File name to import fields from.}
                            {--views-directory= : The name of the directory to create the views under.}
                            {--routes-prefix= : The routes prefix.}
                            {--layout-name=layouts.app : This will extract the validation into a request form class.}
                            {--template-name= : The template name to use when generating the code.}
                            {--force : This option will override the view if one already exists.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an index-views for the model.';

    /**
     * Gets the name of the stub to process.
     *
     * @return string
     */
    protected function getStubName()
    {
        return 'index.blade';
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    protected function handleCreateView()
    {
        $input = $this->getCommandInput();
        $fields = $this->getFields($input->fields, $input->languageFileName, $input->fieldsFile);
        $destenationFile = $this->getDestinationViewFullname($input->viewsDirectory, $input->prefix, 'index');

        if ($this->canCreateView($destenationFile, $input->force, $fields)) {
            $stub = $this->getStub();
            $htmlCreator = $this->getHtmlGenerator($fields, $input->modelName, $this->getTemplateName());

            $this->replaceCommonTemplates($stub, $input)
                 ->replacePrimaryKey($stub, $this->getPrimaryKeyName($fields))
                 ->replaceHeaderCells($stub, $htmlCreator->getIndexHeaderCells())
                 ->replaceBodyCells($stub, $htmlCreator->getIndexBodyCells())
                 ->replaceModelHeader($stub, $this->getHeaderFieldAccessor($fields, $input->modelName))
                 ->createFile($destenationFile, $stub)
                 ->info('Index view was crafted successfully.');
        }
    }

    /**
     * Replaces the column headers in a giving stub.
     *
     * @param string $stub
     * @param string $header
     *
     * @return $this
     */
    protected function replaceHeaderCells(&$stub, $header)
    {
        $stub = $this->strReplace('header_cells', $header, $stub);

        return $this;
    }

    /**
     * Replaces the column cells in a giving stub.
     *
     * @param string $stub
     * @param string $body
     *
     * @return $this
     */
    protected function replaceBodyCells(&$stub, $body)
    {
        $stub = $this->strReplace('body_cells', $body, $stub);

        return $this;
    }
}
