<?php

authenticate_admin();

$plugin_link = "?ng=contaazul_csv/app/";
$element = route(2);
$action = route(3);
$id = route(4);



switch ($element) {
	case 'receber':
    view('app_wrapper', [
      '_include' => 'receber'
    ]);

		break;
	case 'receber-post':

		if(!isset($_FILES['file'])){
			echo json_encode([
				"status" => "error",
				"message" => "Insira o arquivo csv"
			]);
			return ;
		}

		$arquivo = $_FILES['file'];
		$ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
		if( $ext != "csv" ){
			echo json_encode([
				"status" => "error",
				"message" => "Insira o arquivo  do tipo csv"
			]);
			return ;
		}
		$row = 1;
		$conta_azul_receber = ORM::for_table('conta_azul_tratativa_receber');
		$conta_azul_receber->delete_many();
		$sys_cats = ORM::for_table('sys_cats')->where(['type' => 'Income']);
		$sys_cats->delete_many();
		$sys_transactions = ORM::for_table('sys_transactions')->where(['type' => 'Income']);
		$sys_transactions->delete_many();


		if (($handle = fopen($arquivo['tmp_name'], "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 20000, ";")) !== FALSE) {
					if(count($data)<25){
						echo json_encode([
							"status" => "error",
							"message" => "Quantidade incorreta de colunas na linha $row"
						]);
						return ;
					}
					if($row !== 1){
						$conta_azul_receber = ORM::for_table('conta_azul_tratativa_receber')->create();
						$col = 0;
						$conta_azul_receber->id_cliente = $data[$col++];
						$conta_azul_receber->nome_cliente = $data[$col++];
						$conta_azul_receber->cod_referencia = $data[$col++];
						$conta_azul_receber->data_lancamento = $data[$col++];
						//$conta_azul_receber->data_vencimento = $data[4];
						$col++;
						$conta_azul_receber->data_prevista_recebimento = $data[$col++];
						//$conta_azul_receber->data_prevista_recebimento = $data[5];
						$col++;
						$conta_azul_receber->descricao = $data[$col++];
						$conta_azul_receber->valor_original = $data[$col++];
						$conta_azul_receber->forma_recebimento = $data[$col++];
						$conta_azul_receber->valor_recebido = $data[$col++];
						$conta_azul_receber->juros_realizado = $data[$col++];
						$conta_azul_receber->multa_realizada = $data[$col++];
						$conta_azul_receber->desconto_realizado = $data[$col++];
						$conta_azul_receber->valor_total_recebido = $data[$col++];
						$conta_azul_receber->valor_parcela_aberto = $data[$col++];
						$conta_azul_receber->juros_previsto = $data[$col++];
						$conta_azul_receber->multa_prevista = $data[$col++];
						$conta_azul_receber->desconto_previsto = $data[$col++];
						$conta_azul_receber->valor_total_aberto_parcela = $data[$col++];
						$conta_azul_receber->conta_bancaria = $data[$col++];
						$conta_azul_receber->data_ultimo_pagamento = $data[$col++];
						$conta_azul_receber->observacoes = $data[$col++];
						$conta_azul_receber->categoria1 = $data[$col++];
						$conta_azul_receber->valor_cat1 = $data[$col++];
						$conta_azul_receber->centro_custo = $data[$col++];
						$conta_azul_receber->valor_centro1 = $data[$col++];
						if(!$conta_azul_receber->save()){
							echo json_encode([
								"status" => "error",
								"message" => "Erro ao importar linha $row"
							]);
							return ;
						}
					}
		      $row++;
		    }
		    fclose($handle);

				$query_accounts = "INSERT INTO sys_accounts(account, description)
														SELECT catr.conta_bancaria , catr.conta_bancaria
														FROM conta_azul_tratativa_receber catr
														WHERE (SELECT id FROM sys_accounts sa WHERE account COLLATE utf8_unicode_ci like CONCAT(catr.conta_bancaria , '%') LIMIT 1) IS NULL
														GROUP BY catr.conta_bancaria ;";
				ORM::get_db()->exec($query_accounts);

				$query_cats = "INSERT INTO sys_cats(name, type, sorder, total_amount)
														SELECT catr.categoria1, 'Income', 0, 0  from conta_azul_tratativa_receber catr
														WHERE  catr.categoria1 NOT IN(SELECT name  COLLATE utf8_unicode_ci  FROM sys_cats sc )
														GROUP BY  catr.categoria1;";
				ORM::get_db()->exec($query_cats);

				$query_transactions = "INSERT INTO sys_transactions(
																id,
																account,
																account_id,
																type,
																category,
																amount,
																cr,
																status,
																description,
																date,
																currency_iso_code
															)SELECT
																	NULL,
																	catr.conta_bancaria,
																	(SELECT id FROM sys_accounts sa WHERE account COLLATE utf8_unicode_ci like CONCAT(catr.conta_bancaria, '%')  LIMIT 1 ),
																	'Income',
																	catr.categoria1 ,
																	-- catr.valor_original,
																	-- REPLACE(REPLACE(catr.valor_original, '.', ''), ',', '.'),
																	REPLACE(catr.valor_original, ',', ''),
																	-- catr.valor_original,
																	-- REPLACE(REPLACE(catr.valor_original, '.', ''), ',', '.'),
																	REPLACE(catr.valor_original, ',', ''),
																	CASE
																		-- WHEN REPLACE(REPLACE(catr.valor_total_aberto_parcela, '.', ''), ',', '.') = 0
																		WHEN REPLACE(catr.valor_total_aberto_parcela, ',', '') = 0
																		-- WHEN catr.valor_total_aberto_parcela = 0
																		THEN 'Cleared'
																		ELSE 'Uncleared'
																	END status,
																	catr.descricao ,
																	STR_TO_DATE(catr.data_prevista_recebimento , '%d/%m/%Y'),
																	'BRL'
																FROM conta_azul_tratativa_receber catr;";

				ORM::get_db()->exec($query_transactions);

				$query_cats_trans = "UPDATE sys_transactions st
  													 SET st.cat_id = (
															SELECT id
															FROM sys_cats sc
															WHERE sc.name = st.category
															AND sc.type = st.`type`
															LIMIT 1
														);";

				ORM::get_db()->exec($query_cats_trans);

		}

		echo json_encode([
			"status" => "ok",
			"message" => "Arquivo recebido e importado com sucesso"
		]);
		break;

	case 'pagar':
    view('app_wrapper', [
      '_include' => 'pagar',
    ]);

		break;






	// importar contas a pagar pelo csv
	case 'pagar-post':
		if(!isset($_FILES['file'])){
			echo json_encode([
				"status" => "error",
				"message" => "Insira o arquivo csv"
			]);
			return ;
		}

		$arquivo = $_FILES['file'];
		$ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
		if( $ext != "csv" ){
			echo json_encode([
				"status" => "error",
				"message" => "Insira o arquivo  do tipo csv"
			]);
			return ;
		}
		$row = 1;
		$conta_azul_pagar = ORM::for_table('conta_azul_tratativa_pagar');
		$conta_azul_pagar->delete_many();
		$sys_cats = ORM::for_table('sys_cats')->where(['type' => 'Expense']);
		$sys_cats->delete_many();
		$sys_transactions = ORM::for_table('sys_transactions')->where(['type' => 'Expense']);
		$sys_transactions->delete_many();


		if (($handle = fopen($arquivo['tmp_name'], "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 20000, ";")) !== FALSE) {
					if(count($data)<25){
						echo json_encode([
							"status" => "error",
							"message" => "Quantidade incorreta de colunas na linha $row"
						]);
						return ;
					}
					if($row !== 1){
						$col = 0;
						$conta_azul_pagar = ORM::for_table('conta_azul_tratativa_pagar')->create();
						$conta_azul_pagar->id_fornecedor = $data[$col++];
						$conta_azul_pagar->nome_fornecedor = $data[$col++];
						$conta_azul_pagar->cod_referencia = $data[$col++];
						$conta_azul_pagar->data_lancamento = $data[$col++];
						//$conta_azul_pagar->data_vencimento = $data[4];
						$col++;
						$conta_azul_pagar->data_prevista_pagamento = $data[$col++];
						//$conta_azul_pagar->data_prevista_pagamento = $data[5];
						$col++;
						$conta_azul_pagar->descricao = $data[$col++];
						$conta_azul_pagar->valor_original = $data[$col++];
						$conta_azul_pagar->forma_pagamento = $data[$col++];
						$conta_azul_pagar->valor_pago = $data[$col++];
						$conta_azul_pagar->juros_realizado = $data[$col++];
						$conta_azul_pagar->multa_realizada = $data[$col++];
						$conta_azul_pagar->desconto_realizado = $data[$col++];
						$conta_azul_pagar->valor_total_pago = $data[$col++];
						$conta_azul_pagar->valor_parcela_aberto = $data[$col++];
						$conta_azul_pagar->juros_previsto = $data[$col++];
						$conta_azul_pagar->multa_prevista = $data[$col++];
						$conta_azul_pagar->desconto_previsto = $data[$col++];
						$conta_azul_pagar->valor_total_aberto_parcela = $data[$col++];
						$conta_azul_pagar->conta_bancaria = $data[$col++];
						$conta_azul_pagar->data_ultimo_pagamento = $data[$col++];
						$conta_azul_pagar->observacoes = $data[$col++];
						$conta_azul_pagar->categoria1 = $data[$col++];
						$conta_azul_pagar->valor_cat1 = $data[$col++];
						$conta_azul_pagar->centro_custo = $data[$col++];
						$conta_azul_pagar->valor_centro1 = $data[$col++];
						if(!$conta_azul_pagar->save()){
							echo json_encode([
								"status" => "error",
								"message" => "Erro ao importar linha $row"
							]);
							return ;
						}
					}
					$row++;
				}
				fclose($handle);

				$query_accounts = "INSERT INTO sys_accounts(account, description)
														SELECT catr.conta_bancaria , catr.conta_bancaria
														FROM conta_azul_tratativa_pagar catr
														WHERE (SELECT id FROM sys_accounts sa WHERE account COLLATE utf8_unicode_ci like CONCAT(catr.conta_bancaria , '%') LIMIT 1) IS NULL
														GROUP BY catr.conta_bancaria ;";
				ORM::get_db()->exec($query_accounts);

				$query_cats = "INSERT INTO sys_cats(name, type, sorder, total_amount)
														SELECT catr.categoria1, 'Expense', 0, 0  from conta_azul_tratativa_pagar catr
														WHERE  catr.categoria1 NOT IN(SELECT name  COLLATE utf8_unicode_ci  FROM sys_cats sc )
														GROUP BY  catr.categoria1;";
				ORM::get_db()->exec($query_cats);

				$query_transactions = "INSERT INTO sys_transactions(
																id,
																account,
																account_id,
																type,
																category,
																amount,
																dr,
																status,
																description,
																date,
																currency_iso_code
															)SELECT
																	NULL,
																	catr.conta_bancaria,
																	(SELECT id FROM sys_accounts sa WHERE account COLLATE utf8_unicode_ci like CONCAT(catr.conta_bancaria, '%')  LIMIT 1),
																	'Expense',
																	catr.categoria1 ,
																	-- catr.valor_original,
																	-- REPLACE(REPLACE(catr.valor_original, '.', ''), ',', '.'),
																	REPLACE(catr.valor_original, ',', ''),
																	-- catr.valor_original,
																	-- REPLACE(REPLACE(catr.valor_original, '.', ''), ',', '.'),
																	REPLACE(catr.valor_original, ',', ''),
																	CASE
																		-- WHEN REPLACE(REPLACE(catr.valor_total_aberto_parcela, '.', ''), ',', '.') = 0
																		WHEN REPLACE(catr.valor_total_aberto_parcela, ',', '') = 0
																		-- WHEN catr.valor_total_aberto_parcela = 0
																		THEN 'Cleared'
																		ELSE 'Uncleared'
																	END status,
																	catr.descricao ,
																	STR_TO_DATE(catr.data_prevista_pagamento , '%d/%m/%Y'),
																	'BRL'
																FROM conta_azul_tratativa_pagar catr;";

				ORM::get_db()->exec($query_transactions);

				$query_cats_trans = "UPDATE sys_transactions st
														 SET st.cat_id = (
															SELECT id
															FROM sys_cats sc
															WHERE sc.name = st.category
															AND sc.type = st.`type`
															LIMIT 1
														);";

				ORM::get_db()->exec($query_cats_trans);

		}

		echo json_encode([
			"status" => "ok",
			"message" => "Arquivo recebido e importado com sucesso"
		]);
		break;
}
