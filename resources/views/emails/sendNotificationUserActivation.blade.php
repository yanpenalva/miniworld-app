@component('mail::message')

<h1>Ativação de Usuário</h1>

<p>Olá, {{ $user->name }}. Seu cadastro foi ativado no sistema MiniWorld.</p>

<p>Você já pode acessar o sistema para registrar seus projetos, programas ou cursos de extensão.</p>

**Seus dados de acesso:**

- **Link do sistema:** [{{ config('app.url') }}]({{ config('app.url') }})
- **Login:** {{ $user->email }}
- **Senha:** a senha criada durante o cadastro

@component('mail::button', ['url' => config('app.url'), 'color' => 'primary'])
Acessar o Sistema
@endcomponent

Atenciosamente,<br>
Equipe MiniWorld

@endcomponent
