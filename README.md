
### PAGGI plugin WooCommerce

- **Versão realease:** v1.0.0
- **Licensa:** GNU General Public License v3.0

### Descrição
Este é uma extensão para e-comerces que utilizam a plataforma WooCommerce. Aceite pagamentos diramente em sua loja virtual por meio do gatway PAGGI.

### Requisitos
*  PHP 5.3+
*  WordPress 3.8+
*  WooCommerce 2.3+

### Instalação
  * 1) Baixa a ultima versão realease. ([versões realeases](https://github.com/paggi-com/woocommerce-paggi/releases))
  * 2) Descomprima os arquivos e copie para dentro da pasta pasta de plugins do Wordpres (wp-content/plugins/).
  * 3) Faça a ativação do plugin na area administrativa do Wordpres.
  
### Configurar plugin PAGGI Woocomerce

O plugin WooCommerce PAGGI  totalmente transaparente, sendo assim você configura-lo de acordo suas necessidades. Para isso acesso a área de **Plugins** no painel adminstrativo do Wordpress e clique em ```configure``` na opção **WooCommerce Paggi**.
Segue as opcões disponiveis:

#### Geral

 * **Habilitar/Desabilitar** - Mantenha essa opção sempre ativa para que você possa receber pagamentos via PAGGI.
 * **Título** - Esse é o titulo do método de pagamento que o cliente vê durante o checkout.
 * **Descrição** - Descrição do método de pagamento que o cliente verá na sua conta.
 * **Instruções** - Instruções que serão adicionadas à página de agradecimento e aos e-mails.
 
 * **Token** - Informe o seu token para que o processamento dos pagamentos sejam realizadas em produção. Para obte-lo entre em contato com a Paggi.
 
#### Parcelas

 * **Número de parcelas** - Número máximo de parcelas possível com pagamento por cartão de crédito.
 * **Valor mínimo de parcela** - Informe qual o valor mínimo aceito para parcela.
 * **Taxa de juros** - Valor da taxa de juros. Use 0 para parcelamento sem taxa de juros.
 * **Parcelamento sem juros** - Número de parcelas sem juros.
 
#### Desemvolvimento

 * **Sandbox** - Habilite o PAGGI sandbox para testar os pagamentos.
