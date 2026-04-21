@component('mail::message')

<h1>Cadastro Realizado</h1>

<p>Olá, {{ $user->name }}. Seu cadastro foi concluído no sistema MiniWorld.</p>

**Seus dados de primeiro acesso:**

- **Login:** {{ $user->email }}
- **Senha:** {{ $password }}

> **Atenção:** esta é uma senha gerada pelo sistema. Altere-a assim que possível.

@component('mail::button', ['url' => config('app.url'), 'color' => 'primary'])
Acessar o Sistema
@endcomponent

<p>Se você não solicitou este cadastro, ignore este e-mail.</p>

Atenciosamente,<br>
Equipe MiniWorld

@endcomponent
