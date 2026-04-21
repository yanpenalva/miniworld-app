@component('mail::message')

<h1>Confirmação de Cadastro</h1>
<p>Olá, {{ $user->name }}. Obrigado por se cadastrar no sistema MiniWorld.</p>
<p>Você precisa confirmar seu endereço de e-mail e aguardar a aprovação de um administrador.</p>
<p>Clique no botão abaixo para validar seu e-mail:</p>

@component('mail::button', ['url' => $link, 'color' => 'primary'])
Confirmar E-mail
@endcomponent

<p>Se você não concluir a validação, sua conta não será ativada e você não conseguirá acessar o sistema. Este link é
    válido por 48 horas.</p>

<p>Se você não solicitou este cadastro, ignore este e-mail.</p>

Atenciosamente,<br>
Equipe MiniWorld
@endcomponent
