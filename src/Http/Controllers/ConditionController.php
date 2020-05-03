<?php

namespace IziDev\ConditionalField\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class ConditionController extends Controller
{
    public function index(NovaRequest $request)
    {
        $attribute = $request->input('attribute');

        $dependsOn = $this->getValuesDepends($request);

        $resource = $request->newResource();

        $fields = $resource->updateFields($request);
        $field = $fields->findFieldByAttribute($attribute);

        $result = (bool) call_user_func($field->condition, $dependsOn);

        return [
            'result' => $result,
        ];
    }

    private function getValuesDepends($request)
    {
        $dependsOn = [];

        foreach ($request->input('values') as $item) {
            $dependsOn[$item['attribute']] = is_array($item['value']) ? collect($item['value'])->keys()->toArray() : $item['value'];
        }

        return $dependsOn;
    }
}
