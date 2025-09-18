<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Seus dados de acesso</title>
  <style>
    body { font-family: Arial, sans-serif; }
  </style>
</head>
<body>
  <h2>Olá {{ $name }},</h2>
  <p>Você recebeu um convite para acessar o sistema. Para definir sua senha e completar o cadastro, clique no link abaixo:</p>
  <p><a href="{{ $link }}">Definir minha senha</a></p>
  <p>O link expira em 60 minutos.</p>
  <p>Atenciosamente,<br/>Equipe</p>
</body>
</html>
