<?php

/*
|--------------------------------------------------------------------------
| Disable direct access
|--------------------------------------------------------------------------
|
*/

# Make sure admin is logged in
authenticate_admin();

# Make sure table does not exist before creating the table
if (!db_table_exist('conta_azul_tratativa_receber')) {
    DB::schema()->create('conta_azul_tratativa_receber', function ($table) {
      $table->id();
      $table->string('id_cliente')->nullable(); // Identificador do cliente
      $table->string('nome_cliente')->nullable(); // Nome do cliente
      $table->string('cod_referencia')->nullable(); // Código de referência
      $table->string('data_lancamento')->nullable(); // Data do lançamento
      $table->string('data_prevista_recebimento')->nullable(); // Data prevista de recebimento
      $table->string('descricao')->nullable(); // Descrição
      $table->string('valor_original')->nullable(); // Valor original da parcela (R$)
      $table->string('forma_recebimento')->nullable(); // Forma de recebimento
      $table->string('valor_recebido')->nullable(); // Valor recebido da parcela (R$)
      $table->string('juros_realizado')->nullable(); // Juros realizado (R$)
      $table->string('multa_realizada')->nullable(); // Multa realizada (R$)
      $table->string('desconto_realizado')->nullable(); // Desconto realizado (R$)
      $table->string('valor_total_recebido')->nullable(); // Valor total recebido da parcela (R$)
      $table->string('valor_parcela_aberto')->nullable(); // Valor da parcela em aberto (R$)
      $table->string('juros_previsto')->nullable(); // Juros previsto (R$)
      $table->string('multa_prevista')->nullable(); // Multa prevista (R$)
      $table->string('desconto_previsto')->nullable(); // Desconto previsto (R$)
      $table->string('valor_total_aberto_parcela')->nullable(); // Valor total em aberto da parcela (R$)
      $table->string('conta_bancaria')->nullable(); // Conta bancária
      $table->string('data_ultimo_pagamento')->nullable(); // Data do último pagamento
      $table->string('observacoes')->nullable(); // Observações
      $table->string('categoria1')->nullable(); // Categoria 1
      $table->string('valor_cat1')->nullable(); // Valor na Categoria 1
      $table->string('centro_custo')->nullable(); // Centro de Custo 1
      $table->string('valor_centro1')->nullable(); // Valor no Centro de Custo 1
    });
}
if (!db_table_exist('conta_azul_tratativa_pagar')) {
    DB::schema()->create('conta_azul_tratativa_pagar', function ($table) {
      $table->id();
      $table->string('id_fornecedor')->nullable();//Identificador do fornecedor
      $table->string('nome_fornecedor')->nullable();//Nome do fornecedor
      $table->string('cod_referencia')->nullable();//Código de referência
      $table->string('data_lancamento')->nullable();//Data do lançamento
      $table->string('data_prevista_pagamento')->nullable();//Data prevista de pagamento
      $table->string('descricao')->nullable();//Descrição
      $table->string('valor_original')->nullable();//Valor original da parcela (R$)
      $table->string('forma_pagamento')->nullable();//Forma de pagamento
      $table->string('valor_pago')->nullable();//Valor pago da parcela (R$)
      $table->string('juros_realizado')->nullable();//Juros realizado (R$)
      $table->string('multa_realizada')->nullable();//Multa realizada (R$)
      $table->string('desconto_realizado')->nullable();//Desconto realizado (R$)
      $table->string('valor_total_pago')->nullable();//Valor total pago da parcela (R$)
      $table->string('valor_parcela_aberto')->nullable();//Valor da parcela em aberto (R$)
      $table->string('juros_previsto')->nullable();//Juros previsto (R$)
      $table->string('multa_prevista')->nullable();//Multa prevista (R$)
      $table->string('desconto_previsto')->nullable();//Desconto previsto (R$)
      $table->string('valor_total_aberto_parcela')->nullable();//Valor total em aberto da parcela (R$)
      $table->string('conta_bancaria')->nullable();//Conta bancária
      $table->string('data_ultimo_pagamento')->nullable();//Data do último pagamento
      $table->string('observacoes')->nullable();//Observações
      $table->string('categoria1')->nullable();//Categoria 1
      $table->string('valor_cat1')->nullable();//Valor na Categoria 1
      $table->string('centro_custo')->nullable();//Centro de Custo 1
      $table->string('valor_centro1')->nullable();//Valor no Centro de Custo 1
    });
}
