=== PAGGI for WooCommerce ===

Contributors: PAGGI
Donate link: https://paggi.com/
Tags: woocommerce, paggi
Requires at least: 3.8
Tested up to: 5.4
Stable tag: 4.3
Requires PHP: 5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

Este é uma extensão para o PAGGI ecommerce, que utiliza a plataforma WooCommerce. Aceita pagamentos diretamente na loja virtual por meio do gateway PAGGI.

== Description ==

A [Paggi](https://paggi.com/) é a forma prática e fácil para receber pagamentos via cartão de crédito para sua loja WooCommerce. 

= Requisitos =

    *  PHP 5.3+
    *  WordPress 3.8+
    *  WooCommerce 3.3+
    *  Versão mais recente do [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).

= Instalação = 

1. Baixa a ultima versão realease. ([versões realeases](https://github.com/altec-sistemas/paggi-plugin-woocommerce-ecommerce))  
2. Descomprima os arquivos e copie para dentro da pasta pasta de plugins do Wordpress (wp-content/plugins/).  
3. Faça a ativação do plugin na area administrativa do Wordpress.
  

= Configurar plugin PAGGI for WooCommerce =

O plugin ``` PAGGI for WooCommerce ``` é totalmente transaparente, sendo assim você pode configurá-lo de acordo suas necessidades. Para isso acesso a área de **Plugins** no painel adminstrativo do Wordpress e clique em ```configurar``` na opção **PAGGI for WooCommerce**.
Segue as opcões disponiveis:


= Configuração PAGGI gateway = 

 * **PAGGI ID** - Insira seu ID PAGGI para realizar transações.
 * **Habilitar/Desabilitar** - Mantenha essa opção sempre ativa para que você possa receber pagamentos via PAGGI.
 * **Título** - Esse é o titulo do método de pagamento que o cliente vê durante o checkout.
 * **Descrição** - Descrição do método de pagamento que o cliente verá na sua conta.
 * **Instruções** - Instruções que serão adicionadas à página de agradecimento e aos e-mails.
 
 * **PAGGI Token** - Informe o seu token para que o processamento dos pagamentos sejam realizadas em produção. Para obte-lo entre em contato com a PAGGI.
 
= Configuração de Parcelas = 

 * **Número de parcelas** - Número máximo de parcelas possível com pagamento por cartão de crédito.
 * **Valor mínimo de parcela** - Informe qual o valor mínimo aceito para parcelamento de  compras.
 * **Taxa de juros** - Valor da taxa de juros. Use 0 para parcelamento sem taxa de juros.
 * **Parcelamento sem juros** - Número de parcelas sem juros.
 
= Ambiente de Desenvolvimento =

 * **Sandbox** - Habilite o PAGGI Sandbox para testar os pagamentos.
 
 * **Logs** - Habilitar logs de aplicação PAGGI gateway


 == Screenshots ==
 
 1. Configuração Geral:
  ![Alt text](assets/images/cards/screenshots/conf-geral.png?raw=true)

 2. Configuração de parcelamentos:
  ![Alt text](assets/images/cards/screenshots/paggi_checkout.png?raw=true)

 3. Checkout Pagamento PAGGI:
  ![Alt text](assets/images/cards/screenshots/parcelamento.png?raw=true)



Para mais informações entre em contato conosco: contato@paggi.com

== Frequently Asked Questions ==

= Quais são os requisitos para utilizar o PAGGI for WooCommerce? =

    *  PAGGI ID
    *  PAGGI Token
    *  PHP 5.3+
    *  WordPress 3.8+
    *  WooCommerce 3.3+
    *  Versão mais recente do [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/).

== Upgrade Notice ==

* Atualização de legado para sistema novo.

== Changelog == 

= 1.0.0 15/07/2020 =

* Primeira versão do novo plugin.
* Atualização do plugin legado para plugin no sistema novo.

