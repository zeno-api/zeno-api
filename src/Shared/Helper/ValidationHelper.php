<?php

declare(strict_types=1);

namespace Zeno\Shared\Helper;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @author  Iqbal Maulana <iq.bluejack@gmail.com>
 */
trait ValidationHelper
{
    protected function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []): array
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        return $this->extractInputFromRules($request, $rules);
    }

    protected function extractInputFromRules(Request $request, array $rules): array
    {
        return $request->only(collect($rules)->keys()->map(function ($rule) {
            return Str::contains($rule, '.') ? explode('.', $rule)[0] : $rule;
        })->unique()->toArray());
    }

    protected function throwValidationException(Request $request, $validator)
    {
        throw new ValidationException($validator, $this->buildFailedValidationResponse(
            $request, $validator->errors()->getMessages()
        ));
    }

    protected function buildFailedValidationResponse(Request $request, array $errors): JsonResponse
    {
        return new JsonResponse($errors, 422);
    }

    protected function getValidationFactory()
    {
        return app('validator');
    }
}
