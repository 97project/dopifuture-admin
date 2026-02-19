<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(name="Forms", description="Public form display and submission")
 */
class FormApiController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/forms/{slug}",
     *     operationId="formShow",
     *     tags={"Forms"},
     *     summary="Get an active form definition by slug",
     *     description="Returns the form structure including field definitions for rendering on the client side.",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="lang", in="query", @OA\Schema(type="string", enum={"tr","en"})),
     *     @OA\Response(response=200, description="Form definition",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="fields", type="array", @OA\Items(type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="type", type="string", enum={"text","email","textarea","select","checkbox","radio","number","tel","date","file"}),
     *                     @OA\Property(property="label", type="object"),
     *                     @OA\Property(property="required", type="boolean"),
     *                     @OA\Property(property="options", type="array", @OA\Items(type="string"), nullable=true)
     *                 )),
     *                 @OA\Property(property="requires_captcha", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Form not found or inactive")
     * )
     */
    public function show(string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return $this->success([
            'name' => $form->name,
            'slug' => $form->slug,
            'description' => $form->description,
            'fields' => $form->fields,
            'requires_captcha' => $form->requires_captcha,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/forms/{slug}/submit",
     *     operationId="formSubmit",
     *     tags={"Forms"},
     *     summary="Submit a form",
     *     description="Validates submitted data against the form field definitions and stores the submission. Validation rules are dynamically derived from the field types and required flags.",
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="object", description="Key-value pairs where keys are field names",
     *                 @OA\AdditionalProperties(type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Form submitted successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="message", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Form not found or inactive"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function submit(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Dynamic validation from field definitions
        $rules = [];
        foreach ($form->fields as $field) {
            $fieldRules = [];
            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            $typeRules = match ($field['type'] ?? 'text') {
                'email' => ['email'],
                'number' => ['numeric'],
                'tel' => ['string', 'max:20'],
                'textarea' => ['string', 'max:5000'],
                'date' => ['date'],
                'file' => ['file', 'max:5120'],
                default => ['string', 'max:1000'],
            };

            $rules['data.' . $field['name']] = array_merge($fieldRules, $typeRules);
        }

        $validated = $request->validate($rules);

        FormSubmission::create([
            'form_id' => $form->id,
            'data' => $validated['data'] ?? [],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $successMessage = $form->getTranslation('success_message') ?: __('admin.form_submitted');

        return $this->success(['message' => $successMessage], [], 201);
    }
}
