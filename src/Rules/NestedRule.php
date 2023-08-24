<?php

namespace Clickbar\AgGrid\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

abstract class NestedRule implements ValidationRule, ValidatorAwareRule
{
    protected bool $excludeUnvalidated = false;

    /**
     * The root validator instance.
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * The nested validator instance.
     *
     * @var \Illuminate\Validation\Validator|null
     */
    protected $nestedValidator;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // make sure the input is an array
        $data = (array) $value;

        $this->validateNested($attribute, $data);
    }

    abstract public function rules(string $attribute, array $data): array;

    abstract public function attributes(): array;

    final protected function validateNested(string $attribute, array $data): void
    {
        $this->nestedValidator = \Illuminate\Support\Facades\Validator::make(
            $data,
            $this->rules($attribute, $data),
            [],
            $this->attributes()
        );

        $errors = $this->nestedValidator->errors();

        if ($errors->isNotEmpty()) {
            // if the key is part of an array, e.g. key.1.nested, the correct prefix will be key.*.nested
            // which the following regex will produce
            $parentKey = preg_replace('/\.\d+$/', '.*', $attribute);

            // check if the prefix key is set
            if (isset($this->validator->customAttributes[$parentKey])) {
                $messagePrefix = $this->validator->customAttributes[$parentKey].' ';
            } else {
                $messagePrefix = '';
            }

            $messages = collect($errors->messages())->mapWithKeys(function ($messages, $key) use ($attribute, $messagePrefix) {
                $key = $attribute.'.'.$key;
                $messages[0] = $messagePrefix.$messages[0];

                return [$key => $messages];
            })->all();

            $this->validator->messages()->merge($messages);
        } elseif ($this->excludeUnvalidated) {
            $this->validator->setValue($attribute, $this->nestedValidator->validated());
        }
    }

    final public function setValidator(Validator $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    final public function validated(): ?array
    {
        return $this->nestedValidator?->validated();
    }
}
