<p>Olá {{ $sellerName }},</p>

<p>Segue o resumo das suas vendas do dia {{ $date }}:</p>

<ul>
    <li>Quantidade de vendas: {{ $count }}</li>
    <li>Valor total das vendas: R$ {{ number_format($totalValue, 2, ',', '.') }}</li>
    <li>Valor total das comissões: R$ {{ number_format($totalCommission, 2, ',', '.') }}</li>
</ul>

<p>Att,<br/>Equipe</p>
