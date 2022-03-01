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
		require 'models/ContaAzulReceber.php';

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
					if($row !== 1){
						$conta_azul_receber = ORM::for_table('conta_azul_tratativa_receber')->create();
						$conta_azul_receber->id_cliente = $data[0];
						$conta_azul_receber->nome_cliente = $data[1];
						$conta_azul_receber->cod_referencia = $data[2];
						$conta_azul_receber->data_lancamento = $data[3];
						$conta_azul_receber->data_prevista_recebimento = $data[4];
						$conta_azul_receber->descricao = $data[5];
						$conta_azul_receber->valor_original = $data[6];
						$conta_azul_receber->forma_recebimento = $data[7];
						$conta_azul_receber->valor_recebido = $data[8];
						$conta_azul_receber->juros_realizado = $data[9];
						$conta_azul_receber->multa_realizada = $data[10];
						$conta_azul_receber->desconto_realizado = $data[11];
						$conta_azul_receber->valor_total_recebido = $data[12];
						$conta_azul_receber->valor_parcela_aberto = $data[13];
						$conta_azul_receber->juros_previsto = $data[14];
						$conta_azul_receber->multa_prevista = $data[15];
						$conta_azul_receber->desconto_previsto = $data[16];
						$conta_azul_receber->valor_total_aberto_parcela = $data[17];
						$conta_azul_receber->conta_bancaria = $data[18];
						$conta_azul_receber->data_ultimo_pagamento = $data[19];
						$conta_azul_receber->observacoes = $data[20];
						$conta_azul_receber->categoria1 = $data[21];
						$conta_azul_receber->valor_cat1 = $data[22];
						$conta_azul_receber->centro_custo = $data[23];
						$conta_azul_receber->valor_centro1 = $data[24];
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
														WHERE (SELECT id FROM sys_accounts sa WHERE account COLLATE utf8_unicode_ci like CONCAT(catr.conta_bancaria , '%%') LIMIT 1) IS NULL
														GROUP BY catr.conta_bancaria ;";
				ORM::get_db()->exec($query_accounts);

				$query_cats = "INSERT INTO sys_cats(name, type, sorder, total_amount)
														SELECT catr.categoria1, 'Income', 0, 0  from conta_azul_tratativa_receber catr
														WHERE  catr.categoria1 NOT IN(SELECT name  COLLATE utf8_unicode_ci  FROM sys_cats sc )
														GROUP BY  catr.categoria1;";
				ORM::get_db()->exec($query_cats);

				$query_transactions = "INSERT INTO sys_transactions(
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
																	catr.conta_bancaria,
																	(SELECT id FROM sys_accounts sa WHERE account COLLATE utf8_unicode_ci like CONCAT(catr.conta_bancaria, '%%') ),
																	'Income',
																	catr.categoria1 ,
																	REPLACE(REPLACE(catr.valor_original, '.', ''), ',', '.'),
																	REPLACE(REPLACE(catr.valor_original, '.', ''), ',', '.'),
																	CASE
																		WHEN catr.valor_original  = catr.valor_total_recebido  THEN 'Cleared'
																		ELSE 'Uncleared'
																	END status,
																	catr.descricao ,
																	STR_TO_DATE(catr.data_lancamento , '%%d/%%m/%%Y'),
																	'BRL'
																FROM conta_azul_tratativa_receber catr;";

				ORM::get_db()->exec($query_transactions);

		}

		echo json_encode([
			"status" => "ok",
			"message" => "recebido com sucesso"
		]);
		break;

	case 'pagar':
    view('app_wrapper', [
      '_include' => 'pagar',
    ]);

		break;
	case 'pagar-post':
		echo json_encode([
			"status" => "ok",
			"message" => "recebido com sucesso"
		]);
		break;
}
