<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use RuntimeException;

class StoreFileRequest extends FormRequest
{
    public function uploadedFile(): UploadedFile
    {
        $file = $this->file('file');

        if (! $file instanceof UploadedFile) {
            throw new RuntimeException('The validated upload is unavailable.');
        }

        return $file;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $file = $this->file('file');

            if (! $file || ! $file->isValid()) {
                return;
            }

            $clientMime = $file->getClientMimeType();
            $contentMime = $file->getMimeType();

            if ($clientMime !== $contentMime) {
                $validator->errors()->add(
                    'file',
                    'O MIME type informado não corresponde ao conteúdo do arquivo.'
                );
            }
        });
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Selecione um arquivo para enviar.',
            'file.file' => 'O item selecionado deve ser um arquivo válido.',
            'file.max' => 'O arquivo não pode ter mais de 10 MB.',
        ];
    }
}
