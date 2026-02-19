<x-mail::message>
    # {{ __('mail.account_deletion_title') }}

    {{ __('mail.account_deletion_greeting', ['name' => $user->name]) }}

    {{ __('mail.account_deletion_body') }}

    <x-mail::button :url="$confirmationUrl" color="error">
        {{ __('mail.account_deletion_button') }}
    </x-mail::button>

    {{ __('mail.account_deletion_warning') }}

    {{ __('mail.regards') }},<br>
    {{ config('app.name') }}
</x-mail::message>