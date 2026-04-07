<x-mail::message>
# 🎓 Hoş Geldiniz!

Merhaba **{{ $user->name }}**,

DopiFuture platformuna kaydınız başarıyla oluşturuldu. Aşağıdaki bilgilerle giriş yapabilirsiniz:

<x-mail::panel>
**E-posta:** {{ $user->email }}
**Şifre:** {{ $plainPassword }}
</x-mail::panel>

<x-mail::button :url="$loginUrl" color="primary">
Giriş Yap
</x-mail::button>

> ⚠️ Güvenliğiniz için ilk girişte şifrenizi değiştirmenizi öneriyoruz.

Herhangi bir sorunuz varsa destek ekibimizle iletişime geçebilirsiniz.

Saygılarımızla,<br>
**{{ config('app.name') }}**
</x-mail::message>
