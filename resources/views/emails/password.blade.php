@component('mail::message')

<h1>Olá, {{ $name }}!</h1>

<p>Uma solicitação de redefinição de senha foi recebida para sua conta no sistema MiniWorld.</p>

@component('mail::button', ['url' => $link, 'color' => 'primary'])
Redefinir minha senha
@endcomponent

<p>Se você não solicitou a alteração, ignore este e-mail. Sua senha só será alterada após acessar o link acima.</p>

Atenciosamente,<br>
Equipe MiniWorld

@endcomponent
