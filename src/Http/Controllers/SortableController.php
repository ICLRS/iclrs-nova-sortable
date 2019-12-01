<?php

namespace Iclrs\NovaSortable\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Laravel\Nova\Nova;
use \Illuminate\Http\Request;

class SortableController
{
    public function updateOrder(Request $request)
    {
        $resourceId = $request->get('resourceId');
        $resourceName = $request->get('resourceName');
        $prevId = $request->get('prevId');
        $nextId = $request->get('nextId');

        Log::debug([$prevId, $resourceId, $nextId]);

        if (empty($resourceId)) return response()->json(['resourceId' => 'required'], 400);
        if (empty($resourceName)) return response()->json(['resourceName' => 'required'], 400);
        if (empty($prevId) && empty($nextId)) return response()->json(['prevId' => 'required'], 400);

        $resourceClass = Nova::resourceForKey($resourceName);
        if (empty($resourceClass)) return response()->json(['resourceName' => 'invalid'], 400);

        $modelClass = $resourceClass::$model;
        $model = $modelClass::find($resourceId);

        if (!empty($prevId)) {
            $prevModel = $modelClass::find($prevId);
            $model->moveAfter($prevModel);
        } else {
            $nextModel = $modelClass::find($nextId);
            $model->moveBefore($nextModel);
        }

        return response('', 204);
    }

    public function moveToStart(Request $request)
    {
        $validationResult = $this->validateRequest($request);
        if ($validationResult['has_errors'] === true) return response()->json($validationResult['errors'], 400);
        $model = $validationResult['model'];
        $model->moveToStart();
        return response('', 204);
    }

    public function moveToEnd(Request $request)
    {
        $validationResult = $this->validateRequest($request);
        if ($validationResult['has_errors'] === true) return response()->json($validationResult['errors'], 400);
        $model = $validationResult['model'];
        $model->moveToEnd();
        return response('', 204);
    }

    protected function validateRequest(Request $request)
    {
        $resourceId = $request->get('resourceId');
        $resourceName = $request->get('resourceName');

        $errors = [];
        if (empty($resourceId)) $errors['resourceId'] = 'required';
        if (empty($resourceName)) $errors['resourceName'] = 'required';
        if (!empty($resourceName)) {
            $resourceClass = Nova::resourceForKey($resourceName);
            if (empty($resourceClass)) $errors['resourceName'] = 'invalid_name';
            else {
                $modelClass = $resourceClass::$model;
                $model = $modelClass::find($resourceId);
                if (empty($model)) $errors['resourceId'] = 'not_found';
            }
        }

        return [
            'has_errors' => sizeof($errors) > 0,
            'errors' => $errors,
            'model' => isset($model) ? $model : null,
        ];
    }
}
