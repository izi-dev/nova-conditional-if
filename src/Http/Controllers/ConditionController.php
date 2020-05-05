<?php

namespace IziDev\ConditionalField\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class ConditionController extends Controller
{
    protected $fields;

    protected $resource;

    protected $request;

    public function index(NovaRequest $request)
    {
        $this->request = $request;

        $attribute = $this->request->input('attribute');

        $this->resource = $this->request->newResource();

        $this->fields = $this->resource->updateFields($this->request);
        $field = $this->fields->findFieldByAttribute($attribute);

        $dependsOn = $this->getValuesDepends();

        $result = (bool)call_user_func($field->condition, $dependsOn);

        return [
            'result' => $result,
        ];
    }

    private function getValuesDepends()
    {
        $dependsOn = [];

        $model = $this->model();

        foreach ($this->request->input('values') as $item) {
            $value = $this->setValue($model, $item, $item["value"]);

            $dependsOn[$item['attribute']] = $value;
        }

        return $dependsOn;
    }

    public function model()
    {
        $model = $this->resource->model();

        if ($this->request->input('resourceId') !== null) {
            $model = $model->find($this->request->input('resourceId'));
        }

        return $model;
    }

    public function setValue($model, $item, $value)
    {
        if ($this->request->input('isDetail')) {
            $value = $model->{$item['attribute']};

            return $value;
        }

        $model->{$item["attribute"]} = $value;

        return $model->{$item["attribute"]};
    }
}
