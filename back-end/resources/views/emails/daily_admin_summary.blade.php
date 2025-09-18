<p>Olá,</p>

<p>Resumo diário de vendas para {{ $date }}:</p>

<ul>
    <li>Quantidade total de vendas: {{ $totalCount }}</li>
    <li>Valor total das vendas: R$ {{ number_format($totalValue, 2, ',', '.') }}</li>
    <li>Valor total das comissões: R$ {{ number_format($totalCommission, 2, ',', '.') }}</li>
</ul>

<p>Att,<br/>Sistema</p>
