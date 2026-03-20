<?php

namespace App\Http\Requests;

use App\Models\StaffMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('staff_member'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var StaffMember $staffMember */
        $staffMember = $this->route('staff_member');

        return [
            'parent_id' => ['nullable', 'exists:staff_members,id', Rule::notIn([$staffMember->id])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'office_location' => ['nullable', 'string', 'max:255'],
            'show_email_publicly' => ['required', 'boolean'],
            'show_phone_publicly' => ['required', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
            'archived_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'remove_photo' => ['nullable', 'boolean'],
            'translations' => ['required', 'array:'.implode(',', config('app.supported_locales'))],
            'translations.en' => ['required', 'array'],
            'translations.tj' => ['required', 'array'],
            'translations.ru' => ['required', 'array'],
            'translations.*.name' => ['required', 'string', 'max:255'],
            'translations.*.slug' => ['required', 'string', 'max:255'],
            'translations.*.position' => ['nullable', 'string', 'max:255'],
            'translations.*.bio' => ['nullable', 'string'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string'],
            'translations.en.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff_member_translations', 'slug')->where('locale', 'en')->ignore($staffMember->id, 'staff_member_id'),
            ],
            'translations.tj.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff_member_translations', 'slug')->where('locale', 'tj')->ignore($staffMember->id, 'staff_member_id'),
            ],
            'translations.ru.slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff_member_translations', 'slug')->where('locale', 'ru')->ignore($staffMember->id, 'staff_member_id'),
            ],
        ];
    }
}
