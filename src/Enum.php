<?php

namespace TheIdeaBureau\EnumField;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class Enum extends Select
{
    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
        $this->resolveUsing(
            function ($value) {
                return $value instanceof \UnitEnum ? $value->value : $value;
            }
        );

        $this->fillUsing(
            function (NovaRequest $request, $model, $attribute, $requestAttribute) {
                if ($request->exists($requestAttribute)) {
                    $model->{$attribute} = $request[$requestAttribute];
                }
            }
        );
    }

    public function attach($class, $labelMethodName): static
    {
        $this->options($class::array());

        $this->displayUsing(
            function ($value) use ($class, $labelMethodName) {
                if ($value instanceof \UnitEnum) {
					if ($labelMethodName) {
	                    return $value->$labelMethodName();
					}

	            	return $value->name;
                }

                $parsedValue = $class::tryFrom($value);
                if ($parsedValue instanceof \UnitEnum) {
					if ($labelMethodName) {
	                    return $parsedValue->$labelMethodName();
					}

                    return $parsedValue->name;
                }

                return $value;
            }
        );

        $this->rules = [new \Illuminate\Validation\Rules\Enum($class)];
        return $this;
    }
}
